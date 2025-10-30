<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Client;
use App\Models\UnitOfMeasure;
use App\Models\Sale;
use App\Models\Zone;
use App\Models\Product;
use App\Models\{CashSession, CashSessionTransaction,Location, Business};
use Carbon\Carbon;


class ClientController extends Controller
{
    /* public function dashboard(Request $request)
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $client = Client::find($user->client_id);

        if (!$client) {
            abort(403, 'No tienes un cliente asociado');
        }

        // --- 1. Métricas (KPIs) ---
        $stats = [
            'total_pendientes' => $client->sales()->where('status', 'Pendiente')->count(),
            'total_facturados' => $client->sales()->where('status', 'Facturado')->count(),
            'total_entregados' => $client->sales()->where('status', 'Entregado')->count(),
            'gasto_total' => $client->sales()->sum('total'),
        ];
        
        // --- 2. Gráfico de últimos 6 meses ---
        $chartData = [
            'labels' => [],
            'data' => [],
        ];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $chartData['labels'][] = $month->format('M Y');
            $chartData['data'][] = $client->sales()
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('total');
        }

        // --- 3. Últimos pedidos ---
        $latestOrders = $client->sales()
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('client.dashboard', [
            'user' => $user,
            'client' => $client,
            'stats' => $stats,
            'chartData' => $chartData,
            'latestOrders' => $latestOrders,
        ]);
    }*/
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        
        // Simulación: Buscamos el objeto Client asociado al User logueado.
        // **IMPORTANTE**: Ajusta esta lógica para encontrar el Client real.
        $client = Client::find($user->client_id ?? 1); 

        // --- 1. Lógica de Métricas (KPIs) ---
        // Usaremos datos simulados ya que la conexión real requiere modelos de Pedido.
        
        $stats = [
            'total_pendientes' => 24,
            'total_facturados' => 18,
            'total_entregados' => 156,
            'gasto_total' => 2400000,
        ];
        
        // --- 2. Lógica de Gráfico (Simulada) ---
        $chartData = [
            'labels' => ['Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre', 'Enero'],
            'data' => [1800000, 2100000, 1950000, 2300000, 2150000, 2400000],
        ];

        // --- 3. Lógica de Últimos Pedidos (Simulada) ---
        // NOTA: En la realidad, esto sería $client->sales()->latest()->take(3)->get();
        $latestOrders = collect([
            (object)['id' => 101, 'date' => Carbon::parse('2024-01-15'), 'total' => 45200, 'status' => 'Entregado'],
            (object)['id' => 102, 'date' => Carbon::parse('2024-01-18'), 'total' => 32800, 'status' => 'Facturado'],
            (object)['id' => 103, 'date' => Carbon::parse('2024-01-22'), 'total' => 67500, 'status' => 'Pendiente'],
        ]);

        return view('dashboard', [
            'user' => $user,
            'client' => $client,
            'stats' => $stats,
            'chartData' => $chartData,
            'latestOrders' => $latestOrders,
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

        $client = \App\Models\Client::findOrFail($user->client_id);
        Log::info('Cliente encontrado', ['client_id' => $client->id, 'client_name' => $client->name ?? 'N/A']);

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
        
        $products = [];
        if ($b2bLocation) {
            // 2. Si hay una bodega, cargamos los productos y su stock de esa bodega
            $products = Product::query()
                ->join('inventory', function ($join) use ($b2bLocation) {
                    $join->on('products.id', '=', 'inventory.product_id')
                        ->where('inventory.location_id', '=', $b2bLocation->id);
                })
                ->select('products.*', 'inventory.stock as stock_in_location','inventory.stock_minimo as stock_minimo')
                ->with('unitOfMeasure')
                ->get();
                
            Log::info('Productos cargados desde bodega B2B', [
                'total_productos' => $products->count(),
                'location_id' => $b2bLocation->id
            ]);
            
            // Log detallado de productos (opcional, comentar en producción)
            if ($products->isEmpty()) {
                Log::warning('No se encontraron productos en la bodega B2B');
            } else {
                Log::debug('Primeros 5 productos', [
                    'productos' => $products->take(5)->map(function($p) {
                        return [
                            'id' => $p->id,
                            'name' => $p->name ?? 'N/A',
                            'stock' => $p->stock_in_location ?? 0
                        ];
                    })
                ]);
            }
        }
        
        // Categorías sin filtro de business_id
        $categories = Category::get(['id', 'name']);
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
     * Vista de listado de pedidos
     */
    public function listPedidos()
    {
        $user = auth()->user();
        
        if (!$user->client_id) {
            abort(403, 'Acceso no autorizado');
        }

        $client = Client::findOrFail($user->client_id);

        return view('client.pedidos', compact('client'));
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

     // A partir de aquí, se agregarán los nuevos métodos para el carrito y los pedidos.
    // public function addToCart(Request $request) { ... }
    // public function viewCart() { ... }
    // public function storePedido(Request $request) { ... }
    // public function listPedidos() { ... }
}