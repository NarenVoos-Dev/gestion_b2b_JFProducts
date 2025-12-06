<?php

namespace App\Observers;

use App\Models\Product;
use App\Helpers\ProductImageHelper;

class ProductObserver
{
    /**
     * Handle the Product "updating" event.
     * Se ejecuta ANTES de actualizar el producto
     */
    public function updating(Product $product): void
    {
        // Verificar si el nombre cambió y hay una imagen
        if ($product->isDirty('name') && $product->image) {
            $oldName = $product->getOriginal('name');
            $newName = $product->name;
            
            // Renombrar la carpeta y actualizar la ruta de la imagen
            $newImagePath = ProductImageHelper::renameProductFolder($oldName, $newName, $product->image);
            
            if ($newImagePath) {
                $product->image = $newImagePath;
            }
        }
    }

    /**
     * Handle the Product "deleting" event.
     */
    public function deleting(Product $product): void
    {
        // Eliminar la carpeta completa del producto al eliminarlo
        if ($product->image) {
            ProductImageHelper::deleteProductFolder($product->name);
        }
    }
}
