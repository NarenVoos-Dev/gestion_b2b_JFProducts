<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Location;
use App\Models\ProductLot;
use Carbon\Carbon;

class ProductLotSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        echo "🌱 Iniciando seeder de lotes de productos...\n";
        
        // 1. Buscar la bodega B2B
        $b2bLocation = Location::where('is_b2b_warehouse', true)->first();
        
        if (!$b2bLocation) {
            echo "⚠️  No se encontró bodega B2B configurada.\n";
            echo "   Creando bodega B2B por defecto...\n";
            
            $b2bLocation = Location::create([
                'name' => 'Bodega B2B - Catálogo Online',
                'address' => 'Dirección principal',
                'is_b2b_warehouse' => true,
            ]);
            
            echo "✅ Bodega B2B creada: {$b2bLocation->name}\n";
        } else {
            echo "✅ Bodega B2B encontrada: {$b2bLocation->name}\n";
        }
        
        // 2. Obtener productos activos
        $products = Product::where('is_active', true)->get();
        
        if ($products->isEmpty()) {
            echo "⚠️  No hay productos activos. Ejecuta primero ProductSeeder.\n";
            return;
        }
        
        echo "📦 Creando lotes para {$products->count()} productos...\n\n";
        
        $lotCount = 0;
        
        foreach ($products as $product) {
            // Verificar si ya tiene lotes en esta bodega
            $existingLots = ProductLot::where('product_id', $product->id)
                ->where('location_id', $b2bLocation->id)
                ->count();
            
            if ($existingLots > 0) {
                echo "  ⏭️  {$product->name} - Ya tiene {$existingLots} lote(s)\n";
                continue;
            }
            
            // Crear 1-3 lotes por producto con diferentes fechas de vencimiento
            $numberOfLots = rand(1, 3);
            
            for ($i = 1; $i <= $numberOfLots; $i++) {
                $lotNumber = 'LOT-' . date('Y') . '-' . str_pad($product->id, 4, '0', STR_PAD_LEFT) . '-' . $i;
                $quantity = rand(10, 200); // Cantidad aleatoria entre 10 y 200
                $cost = $product->price_regulated_reg * 0.6; // Costo = 60% del precio de venta
                $expirationDate = Carbon::now()->addMonths(rand(6, 36)); // Vence entre 6 y 36 meses
                
                ProductLot::create([
                    'product_id' => $product->id,
                    'location_id' => $b2bLocation->id,
                    'lot_number' => $lotNumber,
                    'quantity' => $quantity,
                    'cost' => $cost,
                    'expiration_date' => $expirationDate,
                ]);
                
                $lotCount++;
            }
            
            echo "  ✅ {$product->name} - {$numberOfLots} lote(s) creado(s)\n";
        }
        
        echo "\n🎉 Seeder completado!\n";
        echo "   Total de lotes creados: {$lotCount}\n";
        echo "   Bodega: {$b2bLocation->name}\n";
        echo "\n💡 Ahora puedes ver los productos en el catálogo B2B: /catalogo\n";
    }
}
