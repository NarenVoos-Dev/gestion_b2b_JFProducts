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
            ->with(['product.unitOfMeasure', 'product.laboratory'])
            ->get();

        $subtotal = $cartItems->sum('subtotal');
        $tax = $subtotal * 0.19; // IVA 19%
        $total = $subtotal + $tax;

        return response()->json([
            'success' => true,
            'cart' => $cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'laboratory' => $item->product->laboratory->name ?? '',
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                    'stock' => $item->product->stock_in_location ?? 0,
                    'image' => $item->product->image ?? '💊',
                ];
            }),
            'summary' => [
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
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

        // Buscar si ya existe en el carrito
        $cartItem = CartItem::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            // Verificar que la nueva cantidad no exceda el stock
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
            // Crear nuevo item
            $cartItem = CartItem::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price_regulated_reg ?? 0,
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
}
