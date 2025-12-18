<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosApiController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\B2BDocumentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas del carrito B2B movidas a web.php para usar sesión web

// Rutas para descargas de documentos B2B
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/b2b/orders/{sale}/download-order', [B2BDocumentController::class, 'downloadOrder']);
    Route::get('/b2b/orders/{sale}/download-invoice', [B2BDocumentController::class, 'downloadInvoice']);
});

