<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController; 
use App\Http\Controllers\PosApiController;

use App\Http\Controllers\ReceiptController;
use App\Http\Middleware\CheckClientAccess; 
use App\Http\Controllers\TemplateController; 

/*
|--------------------------------------------------------------------------
| Rutas Públicas (Landing Page)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Ruta para acceso denegado (cuando el middleware lo redirige)
Route::get('/acceso-denegado', function () {
    return view('acceso-denegado'); 
})->name('acceso-denegado');

// Ruta para imprimir recibos/pedidos (reutilizada del POS)
Route::get('sales/{sale}/receipt', [ReceiptController::class, 'print'])->name('sales.receipt.print');


Route::get('/registro-exitoso', function () {
    return view('registered');
})->name('registered')->middleware('guest');

/*
|--------------------------------------------------------------------------
| Rutas del Portal de Clientes (B2B)
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth', 
    config('jetstream.auth_session'),
    'verified',
    CheckClientAccess::class, //  middleware que verifica el permiso B2B
])->group(function () {
    
    // Dashboard principal del cliente
    Route::get('/dashboard', [ClientController::class, 'dashboard'])->name('dashboard');

    // Módulos del portal de clientes
    
     // Catálogo de Productos
    Route::get('/catalogo', [ClientController::class, 'catalogo'])->name('catalogo');

    Route::get('/productos/{product}', [ClientController::class, 'showProduct'])->name('productos.show');

    // Rutas del Carrito (podría ser un nuevo controlador)
    Route::get('/carrito', [ClientController::class, 'viewCart'])->name('carrito');
    Route::post('/carrito/add', [ClientController::class, 'addToCart'])->name('carrito.add');
    Route::post('/carrito/update', [ClientController::class, 'updateCart'])->name('carrito.update');
    Route::delete('/carrito/remove', [ClientController::class, 'removeFromCart'])->name('carrito.remove');

    // Rutas de Pedidos
    Route::get('/pedidos/checkout', [ClientController::class, 'checkout'])->name('pedidos.checkout');
    Route::post('/pedidos', [ClientController::class, 'storePedido'])->name('pedidos.store');
    Route::get('/pedidos', [ClientController::class, 'listPedidos'])->name('pedidos.list');
    Route::get('/pedidos/{pedido}', [ClientController::class, 'showPedido'])->name('pedidos.show');

    // --- API interna para el Portal de Clientes (protegida por sesión) ---
    Route::prefix('/api/b2b')->name('api.b2b.')->group(function() {
        //Productos
        Route::get('/products', [PosApiController::class, 'searchProductsB2B'])->name('products.search');
        
        //Carrito de compras (Nuevo CartController)
        Route::get('/cart', [\App\Http\Controllers\Api\CartController::class, 'get'])->name('cart.get');
        Route::post('/cart/add', [\App\Http\Controllers\Api\CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/confirm-lot-update', [\App\Http\Controllers\Api\CartController::class, 'confirmLotUpdate'])->name('cart.confirm-lot-update');
        Route::post('/cart/assign-lot', [\App\Http\Controllers\Api\CartController::class, 'assignLot'])->name('cart.assign-lot');
        Route::post('/cart/update', [\App\Http\Controllers\Api\CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/remove/{id}', [\App\Http\Controllers\Api\CartController::class, 'remove'])->name('cart.remove');
        Route::delete('/cart/clear', [\App\Http\Controllers\Api\CartController::class, 'clear'])->name('cart.clear');

        //Pedidos
        Route::post('/pedidos', [PosApiController::class, 'storePedidoB2B'])->name('orders.store');
        Route::post('/checkout', [\App\Http\Controllers\Api\OrderController::class, 'checkout'])->name('checkout');
        Route::get('/pedidos/datatable', [\App\Http\Controllers\Api\OrderController::class, 'datatable'])->name('pedidos.datatable');
        Route::get('/pedidos/summary', [\App\Http\Controllers\Api\OrderController::class, 'summary'])->name('pedidos.summary');
        Route::get('/pedidos/{id}', [\App\Http\Controllers\Api\OrderController::class, 'show'])->name('pedidos.show');
        Route::get('/orders/{sale}/print', [\App\Http\Controllers\B2BDocumentController::class, 'printOrder'])->name('orders.print');
        Route::get('/orders/{sale}/view-invoice', [\App\Http\Controllers\B2BDocumentController::class, 'viewInvoice'])->name('orders.view-invoice');
    });
 
});

/*
|--------------------------------------------------------------------------
| Rutas Administrativas (Para usuarios logueados como Admin)
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth', 
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    
    Route::get('/admin/templates/download-lot-template', [TemplateController::class, 'downloadLotTemplate'])
        ->name('admin.templates.download-lot');

});
