<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Product;

class FixProductImages extends Command
{
    protected $signature = 'products:fix-images';
    protected $description = 'Mueve las imágenes de productos a las carpetas correctas basadas en el nombre del producto';

    public function handle()
    {
        $products = Product::whereNotNull('image')->get();

        $this->info("Encontrados {$products->count()} productos con imágenes\n");

        $moved = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($products as $product) {
            $currentPath = $product->image;
            $correctDirectory = 'products/' . Str::slug($product->name);
            $filename = basename($currentPath);
            $correctPath = $correctDirectory . '/' . $filename;

            $this->line("Producto: {$product->name}");
            $this->line("  Ruta actual: {$currentPath}");
            $this->line("  Ruta correcta: {$correctPath}");

            // Verificar si el archivo existe en la ruta actual
            if (Storage::disk('public')->exists($currentPath)) {
                // Si ya está en la ubicación correcta, saltar
                if ($currentPath === $correctPath) {
                    $this->line("  → Ya está en la ubicación correcta");
                    $skipped++;
                } else {
                    // Crear el directorio correcto si no existe
                    if (!Storage::disk('public')->exists($correctDirectory)) {
                        Storage::disk('public')->makeDirectory($correctDirectory);
                        $this->info("  ✓ Directorio creado: {$correctDirectory}");
                    }

                    // Mover el archivo
                    Storage::disk('public')->move($currentPath, $correctPath);

                    // Actualizar la ruta en la base de datos
                    $product->update(['image' => $correctPath]);

                    $this->info("  ✓ Imagen movida y actualizada");

                    // Eliminar directorio antiguo si está vacío
                    $oldDirectory = dirname($currentPath);
                    if (empty(Storage::disk('public')->files($oldDirectory))) {
                        Storage::disk('public')->deleteDirectory($oldDirectory);
                        $this->info("  ✓ Directorio antiguo eliminado: {$oldDirectory}");
                    }

                    $moved++;
                }
            } else {
                $this->error("  ✗ Archivo no encontrado en: {$currentPath}");
                $errors++;
            }

            $this->line("");
        }

        $this->newLine();
        $this->info("Proceso completado!");
        $this->table(
            ['Resultado', 'Cantidad'],
            [
                ['Movidas', $moved],
                ['Sin cambios', $skipped],
                ['Errores', $errors],
            ]
        );

        return Command::SUCCESS;
    }
}
