<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\ProductLot;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Log;

class SaleObserver
{
    /**
     * Handle the Sale "updated" event.
     */
    public function updated(Sale $sale): void
    {
        // Solo procesar si cambió a "Finalizado" y es B2B
        if ($sale->isDirty('status') && 
            $sale->status === 'Finalizado' && 
            $sale->source === 'b2b' &&
            $sale->getOriginal('status') !== 'Finalizado') {
            
            Log::info("=== Pedido B2B #{$sale->id} finalizado - Descontando inventario ===");
            
            foreach ($sale->items as $item) {
                if ($item->product_lot_id) {
                    // Descontar del lote específico
                    $lot = ProductLot::find($item->product_lot_id);
                    
                    if ($lot) {
                        // Descontar cantidad
                        $lot->decrement('quantity', $item->quantity);
                        
                        // Recargar para obtener quantity actualizada
                        $lot->refresh();
                        
                        // ✅ DESACTIVAR LOTE SI QUEDA EN 0
                        if ($lot->quantity <= 0) {
                            $lot->update(['is_active' => false]);
                            Log::info("✓ Lote {$lot->lot_number} desactivado automáticamente (quantity = 0)");
                        }
                        
                        // Registrar movimiento de stock
                        StockMovement::create([
                            'product_id' => $item->product_id,
                            'product_lot_id' => $item->product_lot_id,
                            'location_id' => $sale->location_id,
                            'type' => 'sale',
                            'quantity' => -$item->quantity,
                            'reference_type' => 'App\Models\Sale',
                            'reference_id' => $sale->id,
                            'notes' => "Venta B2B #$sale->id - Lote: {$lot->lot_number}",
                        ]);
                        
                        Log::info("Stock descontado: Producto {$item->product_id}, Lote {$lot->lot_number}, Cantidad: {$item->quantity}, Stock restante: {$lot->quantity}");
                    } else {
                        Log::warning("Lote {$item->product_lot_id} no encontrado para item {$item->id}");
                    }
                } else {
                    Log::warning("Item {$item->id} del pedido {$sale->id} no tiene lote asignado");
                }
            }
            
            Log::info("=== Inventario actualizado para pedido #{$sale->id} ===");
        }
    }
}
