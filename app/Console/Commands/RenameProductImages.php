<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;

class RenameProductImages extends Command
{
    protected $signature = 'products:rename-images';
    protected $description = 'Renombra las imágenes de productos al formato estándar producto.[ext]';

    public function handle()
    {
        $products = Product::whereNotNull('image')->get();

        $this->info("Encontrados {$products->count()} productos con imágenes\n");

        $renamed = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($products as $product) {
            $currentPath = $product->image;
            $currentFilename = basename($currentPath);
            $directory = dirname($currentPath);
            $extension = pathinfo($currentPath, PATHINFO_EXTENSION);
            $newFilename = "producto.{$extension}";
            $newPath = "{$directory}/{$newFilename}";

            $this->line("Producto: {$product->name}");
            $this->line("  Archivo actual: {$currentFilename}");
            $this->line("  Archivo nuevo: {$newFilename}");

            // Si ya tiene el nombre correcto, saltar
            if ($currentFilename === $newFilename) {
                $this->line("  → Ya tiene el nombre correcto");
                $skipped++;
            } else {
                // Verificar si el archivo existe
                if (Storage::disk('public')->exists($currentPath)) {
                    // Si ya existe un archivo con el nuevo nombre, eliminarlo primero
                    if (Storage::disk('public')->exists($newPath)) {
                        Storage::disk('public')->delete($newPath);
                        $this->line("  ⚠ Archivo existente eliminado: {$newFilename}");
                    }

                    // Renombrar el archivo
                    Storage::disk('public')->move($currentPath, $newPath);

                    // Actualizar la ruta en la base de datos
                    $product->update(['image' => $newPath]);

                    $this->info("  ✓ Imagen renombrada y actualizada");
                    $renamed++;
                } else {
                    $this->error("  ✗ Archivo no encontrado: {$currentPath}");
                    $errors++;
                }
            }

            $this->line("");
        }

        $this->newLine();
        $this->info("Proceso completado!");
        $this->table(
            ['Resultado', 'Cantidad'],
            [
                ['Renombradas', $renamed],
                ['Sin cambios', $skipped],
                ['Errores', $errors],
            ]
        );

        return Command::SUCCESS;
    }
}
