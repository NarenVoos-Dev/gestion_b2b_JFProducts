<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Location;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Procesar checkout del carrito B2B
     */
    public function checkout(Request $request)
    {
        Log::info('=== OrderController@checkout: Inicio ===', [
            'user_id' => Auth::id(),
            'notes' => $request->notes,
        ]);

        DB::beginTransaction();
        
        try {
            // 1. Obtener carrito del usuario
            $cartItems = CartItem::where('user_id', Auth::id())
                ->with('product')
                ->get();
            
            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El carrito está vacío',
                ], 400);
            }
            
            // 2. Calcular totales
            $subtotal = 0;
            $tax = 0;
            $itemCount = $cartItems->count();
            
            foreach ($cartItems as $item) {
                $itemSubtotal = $item->price * $item->quantity;
                $subtotal += $itemSubtotal;
                
                if ($item->product->has_tax) {
                    $taxRate = $item->product->tax_rate ?? 19;
                    $tax += $itemSubtotal * ($taxRate / 100);
                }
            }
            
            $total = $subtotal + $tax;
            
            Log::info('Totales calculados', [
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'item_count' => $itemCount,
            ]);
            
            // 3. Obtener bodega B2B
            $b2bLocation = Location::where('is_b2b_warehouse', true)->first();
            
            if (!$b2bLocation) {
                throw new \Exception('No se encontró la bodega B2B');
            }
            
            // 4. Obtener business_id del usuario
            $user = Auth::user();
            
            // 5. Crear venta (pedido)
            $sale = Sale::create([
                'business_id' => $user->business_id ?? 1, // Default a 1 si no tiene
                'client_id' => $user->client_id,
                'location_id' => $b2bLocation->id,
                'date' => now(),
                'subtotal' => round($subtotal, 2),
                'tax' => round($tax, 2),
                'total' => round($total, 2),
                'is_cash' => false, // B2B siempre es crédito
                'status' => 'Pendiente', // Estado inicial
                'source' => 'b2b',
                'notes' => $request->notes,
            ]);
            
            Log::info('Venta creada', ['sale_id' => $sale->id]);
            
            // 6. Crear items del pedido y sus lotes
            foreach ($cartItems as $cartItem) {
                // Crear el item SIN asignar lote directamente
                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'tax_rate' => $cartItem->product->tax_rate ?? 0,
                    'unit_of_measure_id' => $cartItem->product->unit_of_measure_id,
                ]);
                
                // Si el carrito tiene un lote asignado, crear en sale_item_lots
                if ($cartItem->product_lot_id) {
                    \App\Models\SaleItemLot::create([
                        'sale_item_id' => $saleItem->id,
                        'product_lot_id' => $cartItem->product_lot_id,
                        'quantity' => $cartItem->quantity,
                        'lot_number' => $cartItem->lot_number,
                        'expiration_date' => $cartItem->expiration_date,
                    ]);
                    
                    Log::info("Lote asignado desde carrito", [
                        'sale_item_id' => $saleItem->id,
                        'lot_number' => $cartItem->lot_number,
                        'quantity' => $cartItem->quantity
                    ]);
                }
            }
            
            Log::info('Items del pedido creados', ['item_count' => $itemCount]);
            
            // 7. Limpiar carrito
            CartItem::where('user_id', Auth::id())->delete();
            
            Log::info('Carrito limpiado');
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pedido creado exitosamente',
                'order_id' => $sale->id,
                'order_number' => str_pad($sale->id, 6, '0', STR_PAD_LEFT),
                'total' => round($total, 2),
                'item_count' => $itemCount,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error en checkout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pedido: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Endpoint para DataTable con filtros
     */
    public function datatable(Request $request)
    {
        $user = Auth::user();
        
        $query = Sale::where('client_id', $user->client_id)
            ->where('source', 'b2b');
        
        // Filtros
        if ($request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->order_number) {
            $query->where('id', $request->order_number);
        }
        
        // Búsqueda general (DataTables)
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }
        
        // Total sin filtros
        $totalRecords = Sale::where('client_id', $user->client_id)
            ->where('source', 'b2b')
            ->count();
        
        // Total con filtros
        $filteredRecords = $query->count();
        
        // Ordenamiento
        $orderColumn = $request->order[0]['column'] ?? 1;
        $orderDir = $request->order[0]['dir'] ?? 'desc';
        
        $columns = ['id', 'date', 'status', 'total'];
        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }
        
        // Paginación
        $start = $request->start ?? 0;
        $length = $request->length ?? 25;
        
        $pedidos = $query->skip($start)->take($length)->get();
        
        // Transformar datos para incluir invoice_pdf_path
        $data = $pedidos->map(function($sale) {
            return [
                'id' => $sale->id,
                'date' => $sale->date,
                'status' => $sale->status,
                'subtotal' => $sale->subtotal,
                'tax' => $sale->tax,
                'total' => $sale->total,
                'invoice_pdf_path' => $sale->invoice_pdf_path,
                'invoice_number' => $sale->invoice_number,
            ];
        });
        
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }
    
    /**
     * Endpoint para tarjetas de resumen
     */
    public function summary(Request $request)
    {
        $user = Auth::user();
        
        $query = Sale::where('client_id', $user->client_id)
            ->where('source', 'b2b');
        
        // Aplicar mismos filtros
        if ($request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->order_number) {
            $query->where('id', $request->order_number);
        }
        
        return response()->json([
            'total_pedidos' => $query->count(),
            'pedidos_pendientes' => (clone $query)->where('status', 'Pendiente')->count(),
            'total_gastado' => $query->sum('total'),
        ]);
    }
    
    /**
     * Endpoint para detalle de pedido
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $sale = Sale::where('id', $id)
            ->where('client_id', $user->client_id)
            ->where('source', 'b2b')
            ->with(['items.product.laboratory', 'items.productLot', 'items.lots'])
            ->firstOrFail();
        
        return response()->json($sale);
    }
}