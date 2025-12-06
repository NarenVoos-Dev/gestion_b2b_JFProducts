<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Product;

// Script para mover imágenes de productos a las carpetas correctas

$products = Product::whereNotNull('image')->get();

echo "Encontrados " . $products->count() . " productos con imágenes\n\n";

foreach ($products as $product) {
    $currentPath = $product->image;
    $correctDirectory = 'products/' . Str::slug($product->name);
    $filename = basename($currentPath);
    $correctPath = $correctDirectory . '/' . $filename;
    
    echo "Producto: {$product->name}\n";
    echo "  Ruta actual: {$currentPath}\n";
    echo "  Ruta correcta: {$correctPath}\n";
    
    // Verificar si el archivo existe en la ruta actual
    if (Storage::disk('public')->exists($currentPath)) {
        // Crear el directorio correcto si no existe
        if (!Storage::disk('public')->exists($correctDirectory)) {
            Storage::disk('public')->makeDirectory($correctDirectory);
            echo "  ✓ Directorio creado: {$correctDirectory}\n";
        }
        
        // Mover el archivo
        if ($currentPath !== $correctPath) {
            Storage::disk('public')->move($currentPath, $correctPath);
            
            // Actualizar la ruta en la base de datos
            $product->update(['image' => $correctPath]);
            
            echo "  ✓ Imagen movida y actualizada\n";
            
            // Eliminar directorio antiguo si está vacío
            $oldDirectory = dirname($currentPath);
            if (empty(Storage::disk('public')->files($oldDirectory))) {
                Storage::disk('public')->deleteDirectory($oldDirectory);
                echo "  ✓ Directorio antiguo eliminado: {$oldDirectory}\n";
            }
        } else {
            echo "  → Ya está en la ubicación correcta\n";
        }
    } else {
        echo "  ✗ Archivo no encontrado en: {$currentPath}\n";
    }
    
    echo "\n";
}

echo "Proceso completado!\n";
