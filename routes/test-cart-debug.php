<?php

use Illuminate\Support\Facades\Route;
use App\Models\Product;

// Test route to check cart data
Route::get('/test-cart-data', function() {
    $cart = session()->get('b2b_cart', []);
    
    $output = [
        'cart_count' => count($cart),
        'items' => []
    ];
    
    foreach ($cart as $key => $item) {
        $product = Product::find($item['product_id']);
        
        $output['items'][] = [
            'key' => $key,
            'name' => $item['name'],
            'has_image' => isset($item['image']),
            'has_image_url' => isset($item['image_url']),
            'image_value' => $item['image'] ?? 'NOT SET',
            'image_url_value' => $item['image_url'] ?? 'NOT SET',
            'product_image_url_from_model' => $product ? $product->image_url : 'PRODUCT NOT FOUND',
        ];
    }
    
    return response()->json($output, 200, [], JSON_PRETTY_PRINT);
});
