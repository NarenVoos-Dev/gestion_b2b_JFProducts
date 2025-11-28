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
use App\Models\Zone;
use App\Models\Product;
use App\Models\{CashSession, CashSessionTransaction,Location, Business};
use Carbon\Carbon;


class ClientController extends Controller
{
    
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
        
        $products = collect([]);
        
        if ($b2bLocation) {
            // 2. Usar caché para productos - se actualiza cada 5 minutos o cuando cambia el stock
            $cacheKey = 'b2b_products_location_' . $b2bLocation->id;
            
            $products = Cache::remember($cacheKey, 300, function () use ($b2bLocation) {
                Log::info('Cargando productos desde BD (cache miss)', ['location_id' => $b2bLocation->id]);
                
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
                        
                        // Convertir todo a array directamente
                        $productData = $product->toArray();
                        $productData['lots'] = $lots->toArray();
                        $productData['stock_in_location'] = $stockInLocation;
                        $productData['price'] = $product->price_regulated_reg;
                        
                        return $productData;
                    });
            });
                
            Log::info('Productos cargados (desde cache o BD)', [
                'total_productos' => count($products),
                'location_id' => $b2bLocation->id,
                'cache_key' => $cacheKey
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