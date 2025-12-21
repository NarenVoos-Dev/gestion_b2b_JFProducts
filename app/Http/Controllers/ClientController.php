<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Category;
use App\Models\Client;
use App\Models\UnitOfMeasure;
use App\Models\Sale;
use App\Models\Product;
use App\Models\{CashSession, CashSessionTransaction,Location, Business};
use Carbon\Carbon;


class ClientController extends Controller
{
    
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->client_id) {
            abort(403, 'No tienes acceso al portal B2B');
        }
        
        $client = Client::findOrFail($user->client_id);

        // --- 1. Estadísticas de Pedidos ---
        $stats = [
            'total_pendientes' => Sale::where('client_id', $client->id)
                ->where('source', 'b2b')
                ->where('status', 'Pendiente')
                ->count(),
            'total_proceso' => Sale::where('client_id', $client->id)
                ->where('source', 'b2b')
                ->where('status', 'Separación')
                ->count(),
            'total_entregados' => Sale::where('client_id', $client->id)
                ->where('source', 'b2b')
                ->whereIn('status', ['Entregado', 'Finalizado'])
                ->count(),
            'gasto_total' => Sale::where('client_id', $client->id)
                ->where('source', 'b2b')
                ->sum('total'),
        ];
        
        // --- 2. Saldo de Cuentas por Cobrar ---
        $accountsReceivable = \App\Models\AccountReceivable::where('client_id', $client->id)
            ->where('status', '!=', 'paid')
            ->get();
        
        $accountStats = [
            'total_deuda' => $accountsReceivable->sum('balance'),
            'facturas_pendientes' => $accountsReceivable->count(),
            'facturas_vencidas' => $accountsReceivable->where('status', 'overdue')->count(),
        ];
        
        // --- 3. Gráfico de Gastos Mensuales (últimos 6 meses) ---
        $monthlyData = Sale::where('client_id', $client->id)
            ->where('source', 'b2b')
            ->where('date', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(total) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        $chartData = [
            'labels' => $monthlyData->map(fn($item) => Carbon::parse($item->month . '-01')->format('M Y'))->toArray(),
            'data' => $monthlyData->pluck('total')->toArray(),
        ];

        // --- 4. Últimos Pedidos ---
        $latestOrders = Sale::where('client_id', $client->id)
            ->where('source', 'b2b')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();
        
        // --- 5. Estadísticas de Crédito ---
        $creditStats = $client->getCreditStats();
        
        // Próximo vencimiento
        $nextDueDate = \App\Models\AccountReceivable::where('client_id', $client->id)
            ->where('status', '!=', 'paid')
            ->whereNotNull('due_date')
            ->orderBy('due_date', 'asc')
            ->value('due_date');

        return view('dashboard', [
            'user' => $user,
            'client' => $client,
            'stats' => $stats,
            'accountStats' => $accountStats,
            'chartData' => $chartData,
            'latestOrders' => $latestOrders,
            'creditStats' => $creditStats,
            'nextDueDate' => $nextDueDate,
        ]);
    }

     /**
     * Vista del catálogo de productos para clientes B2B
     */
    public function catalogo()
    {
        Log::info('=== Inicio de catalogo() ===');
        
        $user = auth()->user();
        Log::info('Usuario autenticado', ['user_id' => $user->id, 'client_id' => $user->client_id]);
        
        if (!$user->client_id) {
            Log::warning('Acceso denegado: usuario sin client_id', ['user_id' => $user->id]);
            abort(403, 'Acceso no autorizado');
        }

        $client = \App\Models\Client::with('priceList')->findOrFail($user->client_id);
        Log::info('Cliente encontrado', [
            'client_id' => $client->id, 
            'client_name' => $client->name ?? 'N/A',
            'price_list_id' => $client->price_list_id,
            'price_list_percentage' => $client->priceList->percentage ?? 0
        ]);

        // Obtener porcentaje de la lista de precios del cliente (0 si no tiene)
        $pricePercentage = $client->priceList ? $client->priceList->percentage : 0;

        // 1. Buscamos la bodega designada para el catálogo B2B
        $b2bLocation = Location::where('is_b2b_warehouse', true)->first();
        
        if ($b2bLocation) {
            Log::info('Bodega B2B encontrada', [
                'location_id' => $b2bLocation->id,
                'location_name' => $b2bLocation->name ?? 'N/A'
            ]);
        } else {
            Log::warning('No se encontró bodega B2B configurada');
        }
        
        $products = collect([]);
        
        if ($b2bLocation) {
            // 2. Cachear productos BASE (sin precios personalizados)
            $cacheKey = 'b2b_products_location_' . $b2bLocation->id;
            
            $productsBase = Cache::remember($cacheKey, 300, function () use ($b2bLocation) {
                Log::info('Cargando productos desde BD (cache miss)', [
                    'location_id' => $b2bLocation->id
                ]);
                
                return Product::query()
                    ->where('is_active', true)
                    ->with(['unitOfMeasure', 'category', 'laboratory', 'molecule', 'pharmaceuticalForm'])
                    ->get()
                    ->map(function ($product) use ($b2bLocation) {
                        // Calcular stock en la bodega B2B sumando los lotes
                        $stockInLocation = \App\Models\ProductLot::where('product_id', $product->id)
                            ->where('location_id', $b2bLocation->id)
                            ->sum('quantity');
                        
                        // Cargar lotes de esta bodega
                        $lots = \App\Models\ProductLot::where('product_id', $product->id)
                            ->where('location_id', $b2bLocation->id)
                            ->where('quantity', '>', 0)
                            ->orderBy('expiration_date', 'asc')
                            ->get(['lot_number', 'quantity', 'expiration_date', 'cost']);
                        
                        // Obtener el costo del lote MÁS CARO (mayor valor) que tenga cost > 0
                        $maxCost = $lots->where('cost', '>', 0)->max('cost') ?? 0;
                        
                        // Convertir a array SIN aplicar porcentaje
                        $productData = $product->toArray();
                        $productData['lots'] = $lots->toArray();
                        $productData['stock_in_location'] = $stockInLocation;
                        $productData['base_cost'] = $maxCost; // Costo del lote más caro
                        
                        return $productData;
                    });
            });
            
            // 3. Aplicar porcentaje DESPUÉS del caché (personalizado por cliente)
            $products = $productsBase->map(function ($productData) use ($pricePercentage) {
                $baseCost = $productData['base_cost'] ?? 0;
                $priceRegulated = $productData['price_regulated_reg'] ?? null;
                
                // Fórmula correcta de Markup: Precio = Base / (1 - %/100)
                // Ejemplo: Si base = 10,000 y % = 20, entonces: 10,000 / (1 - 0.20) = 10,000 / 0.80 = 12,500
                if ($pricePercentage >= 100) {
                    // Evitar división por cero o negativo
                    $priceWithIncrease = $baseCost;
                } else {
                    $priceWithIncrease = $baseCost / (1 - ($pricePercentage / 100));
                }
                
                // VALIDACIÓN: Si supera el precio regulado, establecer en regulado - 1000
                if ($priceRegulated && $priceWithIncrease > $priceRegulated) {
                    $finalPrice = $priceRegulated - 1000;
                    $productData['price_capped'] = true; // Indicador de que se aplicó tope
                } else {
                    $finalPrice = $priceWithIncrease;
                    $productData['price_capped'] = false;
                }
                
                $productData['price'] = round($finalPrice, 2);
                $productData['base_price'] = $baseCost; // Para referencia
                $productData['price_percentage'] = $pricePercentage;
                $productData['price_regulated'] = $priceRegulated; // Para referencia
                
                return $productData;
            });
                
            Log::info('Productos cargados (desde cache o BD)', [
                'total_productos' => count($products),
                'location_id' => $b2bLocation->id,
                'cache_key' => $cacheKey,
                'price_percentage_applied' => $pricePercentage
            ]);
        }
        
        // Categorías también en caché
        $categories = Cache::remember('b2b_categories', 600, function() {
            return Category::get(['id', 'name']);
        });
        
        Log::info('Categorías cargadas', ['total_categorias' => $categories->count()]);
        
        $cartCount = count(session()->get('b2b_cart', []));
        Log::info('Carrito B2B', ['items_count' => $cartCount]);
        
        Log::info('=== Fin de catalogo() - Retornando vista ===');
        
        return view('client.catalogo', compact('categories', 'client', 'cartCount', 'products'));
    }

    /**
     * Vista del carrito de compras
     */
    public function viewCart()
    {
        Log::info('=== Inicio viewCart ===');
        
        $user = auth()->user();
        Log::info('Usuario accediendo al carrito', [
            'user_id' => $user->id,
            'client_id' => $user->client_id
        ]);
        
        if (!$user->client_id) {
            Log::warning('Acceso denegado: usuario sin client_id', ['user_id' => $user->id]);
            abort(403, 'Acceso no autorizado');
        }

        $client = Client::findOrFail($user->client_id);
        Log::info('Cliente validado', ['client_id' => $client->id, 'client_name' => $client->name ?? 'N/A']);
        
        $cart = session()->get('b2b_cart', []);
        $cartCount = count($cart);
        
        Log::info('Carrito obtenido', [
            'total_items' => $cartCount,
            'session_id' => substr(session()->getId(), 0, 10) . '...'
        ]);

        // Calculamos los totales en el backend
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
        }
        
        Log::info('Totales calculados', [
            'items_count' => $cartCount,
            'subtotal' => $subtotal
        ]);
        
        if ($cartCount === 0) {
            Log::info('Usuario viendo carrito vacío');
        }
        
        Log::info('=== Fin viewCart - Vista renderizada ===');

        return view('client.cart', compact('client', 'cart', 'subtotal'));
    }
     /**
     * Vista de detalle de un pedido específico
     */
    public function showPedido($id)
    {
        $user = auth()->user();
        
        if (!$user->client_id) {
            abort(403, 'Acceso no autorizado');
        }

        $sale = Sale::where('id', $id)
            ->where('client_id', $user->client_id)
            ->with(['items.product', 'items.unitOfMeasure'])
            ->firstOrFail();

        return view('client.pedido-detail', compact('sale'));
    }

   
    /**
     * Mostrar página de checkout
     */
    public function checkout()
    {
        return view('client.checkout');
    }
    
    public function listPedidos()
    {
        $user = Auth::user();
        
        $pedidos = Sale::where('client_id', $user->client_id)
            ->where('source', 'b2b')
            ->orderBy('date', 'desc')
            ->get();
        
        return view('client.pedidos', compact('pedidos'));
    }
    
    /**
     * Mostrar cuentas por pagar del cliente
     */
    public function cuentasPagar()
    {
        $user = Auth::user();
        $client = Client::find($user->client_id);
        
        if (!$client) {
            abort(403, 'Cliente no encontrado');
        }
        
        // Obtener cuentas por cobrar del cliente (pendientes y parciales)
        $accounts = \App\Models\AccountReceivable::where('client_id', $client->id)
            ->whereIn('status', ['pending', 'partial'])
            ->with(['sale', 'payments'])
            ->orderBy('due_date', 'asc')
            ->paginate(10);
        
        // Calcular saldo total
        $totalBalance = \App\Models\AccountReceivable::where('client_id', $client->id)
            ->whereIn('status', ['pending', 'partial'])
            ->sum('balance');
        
        return view('client.cuentas-pagar', compact('accounts', 'totalBalance'));
    }
    
    /**
     * Subir comprobante de pago
     */
    public function uploadPaymentProof(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts_receivable,id',
            'payment_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120' // 5MB
        ]);
        
        $user = Auth::user();
        $account = \App\Models\AccountReceivable::findOrFail($request->account_id);
        
        // Verificar que la cuenta pertenece al cliente
        if ($account->client_id !== $user->client_id) {
            return back()->with('error', 'No tienes permiso para subir comprobantes a esta cuenta');
        }
        
        // Guardar archivo
        $path = $request->file('payment_proof')->store('payment-proofs', 'local');
        
        // Crear registro de pago pendiente de aprobación
        \App\Models\AccountPayment::create([
            'account_receivable_id' => $account->id,
            'amount' => 0, // El admin lo completará
            'payment_method' => 'Transferencia',
            'payment_date' => now(),
            'payment_proof_path' => $path,
            'notes' => 'Comprobante subido por cliente - Pendiente de verificación',
            'created_by' => $user->id,
        ]);
        
        // Notificaciones desactivadas - solo badge en menú
        /*
        $adminUsers = \App\Models\User::role('admin')->get();
        $client = \App\Models\Client::find($user->client_id);
        
        foreach ($adminUsers as $admin) {
            \Filament\Notifications\Notification::make()
                ->title('Nuevo comprobante de pago subido')
                ->body("{$client->name} ha subido un comprobante para la factura {$account->invoice_number}")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('review')
                        ->label('Revisar')
                        ->url(route('filament.admin.resources.account-receivables.pages.manage-payments', $account))
                        ->markAsRead()
                ])
                ->sendToDatabase($admin);
        }
        */
        
        return back()->with('success', 'Comprobante subido exitosamente. Será revisado por el administrador.');
    }
}