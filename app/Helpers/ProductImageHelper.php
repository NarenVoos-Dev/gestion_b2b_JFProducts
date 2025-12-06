<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class ProductImageHelper
{
    /**
     * Obtener el directorio de imágenes para un producto basado en su nombre
     */
    public static function getProductImageDirectory(string $productName): string
    {
        $slug = Str::slug($productName);
        return "products/{$slug}";
    }

    /**
     * Guardar imagen de producto
     */
    public static function saveProductImage(UploadedFile $file, string $productName, ?string $oldImagePath = null): string
    {
        $directory = self::getProductImageDirectory($productName);
        $extension = $file->getClientOriginalExtension();
        $filename = "producto.{$extension}";
        
        // Eliminar imagen anterior si existe
        if ($oldImagePath) {
            self::deleteProductImage($oldImagePath);
        }
        
        // Guardar nueva imagen
        $path = $file->storeAs($directory, $filename, 'public');
        
        return $path;
    }

    /**
     * Eliminar imagen de producto
     */
    public static function deleteProductImage(string $imagePath): bool
    {
        if (Storage::disk('public')->exists($imagePath)) {
            return Storage::disk('public')->delete($imagePath);
        }
        return false;
    }

    /**
     * Renombrar carpeta de producto cuando cambia el nombre
     */
    public static function renameProductFolder(string $oldName, string $newName, ?string $currentImagePath = null): ?string
    {
        if (!$currentImagePath) {
            return null;
        }

        $oldDirectory = self::getProductImageDirectory($oldName);
        $newDirectory = self::getProductImageDirectory($newName);

        // Si los directorios son iguales, no hacer nada
        if ($oldDirectory === $newDirectory) {
            return $currentImagePath;
        }

        // Verificar si existe la carpeta antigua
        if (!Storage::disk('public')->exists($oldDirectory)) {
            return $currentImagePath;
        }

        // Mover todos los archivos de la carpeta antigua a la nueva
        $files = Storage::disk('public')->files($oldDirectory);
        
        foreach ($files as $file) {
            $filename = basename($file);
            $newPath = "{$newDirectory}/{$filename}";
            
            Storage::disk('public')->move($file, $newPath);
        }

        // Eliminar carpeta antigua si quedó vacía
        if (empty(Storage::disk('public')->allFiles($oldDirectory))) {
            Storage::disk('public')->deleteDirectory($oldDirectory);
        }

        // Retornar nueva ruta de la imagen
        $filename = basename($currentImagePath);
        return "{$newDirectory}/{$filename}";
    }

    /**
     * Obtener URL completa de la imagen
     */
    public static function getProductImageUrl(?string $imagePath): string
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            return asset('storage/' . $imagePath);
        }
        
        return asset('img/no-image.png');
    }

    /**
     * Eliminar carpeta completa del producto
     */
    public static function deleteProductFolder(string $productName): bool
    {
        $directory = self::getProductImageDirectory($productName);
        
        if (Storage::disk('public')->exists($directory)) {
            return Storage::disk('public')->deleteDirectory($directory);
        }
        
        return false;
    }
}
