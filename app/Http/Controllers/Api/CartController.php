<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Obtener el carrito del usuario autenticado
     */
    public function get()
    {
        $cartItems = CartItem::where('user_id', Auth::id())
            ->with('product:id,has_tax,tax_rate')  // Solo cargar campos necesarios del producto
            ->get();

        $subtotal = 0;
        $tax = 0;
        
        // Calcular subtotal e IVA por producto
        foreach ($cartItems as $item) {
            $itemSubtotal = $item->price * $item->quantity;
            $subtotal += $itemSubtotal;
            
            // Si el producto tiene IVA, calcularlo
            if ($item->product->has_tax) {
                $taxRate = $item->product->tax_rate ?? 19; // Default 19%
                $tax += $itemSubtotal * ($taxRate / 100);
            }
        }
        
        $total = $subtotal + $tax;

        return response()->json([
            'success' => true,
            'cart' => $cartItems->map(function ($item) {
                $itemSubtotal = $item->price * $item->quantity;
                $itemTax = 0;
                $taxRate = 0;
                
                if ($item->product->has_tax) {
                    $taxRate = $item->product->tax_rate ?? 19;
                    $itemTax = $itemSubtotal * ($taxRate / 100);
                }
                
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product_name,  // Usar campo guardado
                    'laboratory' => $item->laboratory ?? 'Sin laboratorio',  // Usar campo guardado
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $itemSubtotal,
                    'tax' => $itemTax,
                    'tax_rate' => $taxRate,
                    'has_tax' => $item->product->has_tax,
                    'image_url' => $item->image_url ?? asset('img/no-image.png'),  // Usar campo guardado
                    'lot_number' => $item->lot_number,
                    'expiration_date' => $item->expiration_date?->format('Y-m-d'),
                    'product_lot_id' => $item->product_lot_id,
                ];
            }),
            'summary' => [
                'subtotal' => round($subtotal, 2),
                'tax' => round($tax, 2),
                'total' => round($total, 2),
                'item_count' => $cartItems->count(),
            ]
        ]);
    }

    /**
     * Agregar producto al carrito
     */
    public function add(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('=== CartController@add: Inicio ===', [
            'user_id' => Auth::id(),
            'request' => $request->all()
        ]);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1',
            'product_lot_id' => 'nullable|exists:product_lots,id',  // Lote OPCIONAL
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;

        // Verificar stock disponible - Obtener bodega B2B directamente de la BD
        $b2bLocation = \App\Models\Location::where('is_b2b_warehouse', true)->first();
        
        if (!$b2bLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Bodega B2B no configurada',
            ], 500);
        }
        
        $stock = $product->productLots()
            ->where('location_id', $b2bLocation->id)
            ->sum('quantity');

        \Illuminate\Support\Facades\Log::info('=== CartController@add: Verificación de Stock ===', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'location_id' => $b2bLocation->id,
            'location_name' => $b2bLocation->name,
            'stock_found' => $stock
        ]);

        if ($stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuficiente. Disponible: ' . $stock . ' unidades',
            ], 400);
        }

        // Buscar si ya existe en el carrito (mismo producto, cualquier lote)
        $cartItem = CartItem::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            // CASO 1: Item existe SIN lote y ahora se seleccionó un lote
            if ($cartItem->product_lot_id === null && $request->product_lot_id) {
                // Obtener información del lote
                $lot = \App\Models\ProductLot::find($request->product_lot_id);
                
                // Validar stock del lote
                if ($lot && $quantity > $lot->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuficiente en el lote seleccionado. Disponible: {$lot->quantity} unidades",
                    ], 400);
                }
                
                // Devolver respuesta especial pidiendo confirmación
                return response()->json([
                    'success' => false,
                    'requires_confirmation' => true,
                    'message' => 'Ya tienes este producto en el carrito sin lote asignado. ¿Deseas actualizar con el lote seleccionado?',
                    'cart_item_id' => $cartItem->id,
                    'current_quantity' => $cartItem->quantity,
                    'new_quantity' => $quantity,
                    'lot_id' => $lot->id,
                    'lot_number' => $lot->lot_number,
                ], 200);
            }
            
            // CASO 2: Simplemente sumar cantidad (mismo lote o ambos sin lote)
            $newQuantity = $cartItem->quantity + $quantity;
            if ($newQuantity > $stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuficiente. Disponible: ' . $stock . ' unidades',
                ], 400);
            }
            
            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        } else {
            // Obtener cliente y su lista de precios
            $user = Auth::user();
            $client = \App\Models\Client::with('priceList')->find($user->client_id);
            $pricePercentage = $client && $client->priceList ? $client->priceList->percentage : 0;
            
            // Obtener costo del lote más caro
            $maxCost = \App\Models\ProductLot::where('product_id', $product->id)
                ->where('location_id', $b2bLocation->id)
                ->where('quantity', '>', 0)
                ->where('cost', '>', 0)
                ->max('cost') ?? 0;
            
            // Calcular precio con fórmula correcta: Base / (1 - %/100)
            if ($pricePercentage >= 100) {
                $calculatedPrice = $maxCost;
            } else {
                $calculatedPrice = $maxCost / (1 - ($pricePercentage / 100));
            }
            
            // Validar contra precio regulado
            $priceRegulated = $product->price_regulated_reg ?? null;
            if ($priceRegulated && $calculatedPrice > $priceRegulated) {
                $finalPrice = $priceRegulated - 1000;
            } else {
                $finalPrice = $calculatedPrice;
            }
            
            // Obtener información del lote si fue seleccionado
            $lot = null;
            if ($request->product_lot_id) {
                $lot = \App\Models\ProductLot::find($request->product_lot_id);
                
                // Validar que el lote tenga stock suficiente
                if ($lot && $quantity > $lot->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuficiente en el lote seleccionado. Disponible: {$lot->quantity} unidades",
                    ], 400);
                }
            }
            
            // Crear nuevo item
            $cartItem = CartItem::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => round($finalPrice, 2),
                'product_name' => $product->name,
                'image_url' => $product->image_url,
                'laboratory' => $product->laboratory?->name,
                'product_lot_id' => $lot?->id,
                'lot_number' => $lot?->lot_number,
                'expiration_date' => $lot?->expiration_date,
            ]);
        }

        $cartCount = CartItem::where('user_id', Auth::id())->count();

        return response()->json([
            'success' => true,
            'message' => 'Producto agregado al carrito',
            'cart_count' => $cartCount,
            'item' => $cartItem,
        ]);
    }

    /**
     * Actualizar cantidad de un item del carrito
     */
    public function update(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::where('id', $request->cart_item_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Verificar stock disponible
        $b2bLocation = \App\Models\Location::where('is_b2b_warehouse', true)->first();
        
        if (!$b2bLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Bodega B2B no configurada',
            ], 500);
        }
        
        $stock = $cartItem->product->productLots()
            ->where('location_id', $b2bLocation->id)
            ->sum('quantity');

        if ($request->quantity > $stock) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuficiente. Disponible: ' . $stock . ' unidades',
            ], 400);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Cantidad actualizada',
            'item' => $cartItem,
        ]);
    }

    /**
     * Eliminar un item del carrito
     */
    public function remove($id)
    {
        $cartItem = CartItem::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $cartItem->delete();

        $cartCount = CartItem::where('user_id', Auth::id())->count();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado del carrito',
            'cart_count' => $cartCount,
        ]);
    }

    /**
     * Limpiar todo el carrito
     */
    public function clear()
    {
        CartItem::where('user_id', Auth::id())->delete();

        return response()->json([
            'success' => true,
            'message' => 'Carrito vaciado',
            'cart_count' => 0,
        ]);
    }

    /**
     * Confirmar actualización de lote en item existente
     */
    public function confirmLotUpdate(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'lot_id' => 'required|exists:product_lots,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::where('id', $request->cart_item_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $lot = \App\Models\ProductLot::findOrFail($request->lot_id);

        // Actualizar item con información del lote y sumar cantidad
        $cartItem->update([
            'quantity' => $cartItem->quantity + $request->quantity,
            'product_lot_id' => $lot->id,
            'lot_number' => $lot->lot_number,
            'expiration_date' => $lot->expiration_date,
        ]);

        $cartCount = CartItem::where('user_id', Auth::id())->count();

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado con lote seleccionado',
            'cart_count' => $cartCount,
        ]);
    }

    /**
     * Asignar lote a item existente (desde el carrito)
     */
    public function assignLot(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'lot_id' => 'required|exists:product_lots,id',
        ]);

        $cartItem = CartItem::where('id', $request->cart_item_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $lot = \App\Models\ProductLot::findOrFail($request->lot_id);

        // Actualizar item con información del lote (SIN cambiar cantidad)
        $cartItem->update([
            'product_lot_id' => $lot->id,
            'lot_number' => $lot->lot_number,
            'expiration_date' => $lot->expiration_date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lote asignado correctamente',
        ]);
    }
}
