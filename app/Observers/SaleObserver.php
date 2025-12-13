<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\ProductLot;
use App\Models\StockMovement;
use App\Models\AccountReceivable;
use Illuminate\Support\Facades\Log;

class SaleObserver
{
    /**
     * Handle the Sale "updated" event.
     */
    public function updated(Sale $sale): void
    {
        // CREAR CUENTA POR COBRAR Y DESCONTAR INVENTARIO cuando cambia a Facturado
        if ($sale->isDirty('status') && 
            $sale->status === 'Facturado' && 
            $sale->source === 'b2b' &&
            $sale->getOriginal('status') !== 'Facturado') {
            
            Log::info("=== Pedido B2B #{$sale->id} facturado - Procesando cuenta e inventario ===");
            
            // Verificar si ya existe una cuenta por cobrar
            if ($sale->accountReceivable) {
                Log::warning("El pedido #{$sale->id} ya tiene una cuenta por cobrar creada");
                throw new \Exception("Este pedido ya fue facturado anteriormente. Número de factura: {$sale->accountReceivable->invoice_number}");
            }
            
            // 1. Crear cuenta por cobrar
            $this->createAccountReceivable($sale);
            
            // 2. Descontar inventario
            $this->deductInventory($sale);
        }
        
        // NO HACER NADA si cambia a Finalizado (ya todo procesado en Facturado)
        if ($sale->isDirty('status') && 
            $sale->status === 'Finalizado' &&
            $sale->source === 'b2b') {
            Log::info("Pedido B2B #{$sale->id} marcado como finalizado (cuenta e inventario ya procesados)");
        }
        
        // NO HACER NADA si se cancela
        if ($sale->isDirty('status') && $sale->status === 'Cancelado') {
            Log::info("Pedido #{$sale->id} cancelado - No se crea cuenta ni se descuenta inventario");
        }
    }
    
    /**
     * Crear cuenta por cobrar para el pedido
     */
    protected function createAccountReceivable(Sale $sale): void
    {
        // Cargar relación lots para validar
        $sale->load('items.lots');
        
        // Validar que todos los items tengan lotes asignados en sale_item_lots
        $itemsWithoutLot = 0;
        foreach ($sale->items as $item) {
            if ($item->lots->count() === 0) {
                $itemsWithoutLot++;
            }
        }
        
        if ($itemsWithoutLot > 0) {
            Log::error("No se puede facturar pedido #{$sale->id}: {$itemsWithoutLot} items sin lote asignado");
            throw new \Exception("No se puede facturar: {$itemsWithoutLot} items sin lote asignado");
        }
        
        // Obtener el número de factura de la sesión (ingresado manualmente)
        $invoiceNumber = session('pending_invoice_number_' . $sale->id);
        
        // Si no hay número en sesión, generar uno automático (fallback)
        if (empty($invoiceNumber)) {
            $invoiceNumber = 'INV-' . date('Ym') . '-' . str_pad($sale->id, 6, '0', STR_PAD_LEFT);
            Log::warning("No se encontró número de factura manual para pedido #{$sale->id}, usando automático: {$invoiceNumber}");
        }
        
        // Limpiar la sesión
        session()->forget('pending_invoice_number_' . $sale->id);
        
        AccountReceivable::create([
            'sale_id' => $sale->id,
            'client_id' => $sale->client_id,
            'invoice_number' => $invoiceNumber,
            'amount' => $sale->total,
            'balance' => $sale->total,
            'due_date' => now()->addDays(30), // 30 días de plazo
            'status' => 'pending',
            'notes' => 'Generada automáticamente desde pedido B2B #' . $sale->id,
        ]);
        
        Log::info("✓ Cuenta por cobrar creada: {$invoiceNumber}, Monto: {$sale->total}");
    }
    
    /**
     * Descontar inventario del pedido
     */
    protected function deductInventory(Sale $sale): void
    {
        // Cargar la relación lots para todos los items
        $sale->load('items.lots');
        
        foreach ($sale->items as $item) {
            // Verificar si tiene lotes asignados en sale_item_lots
            if ($item->lots->count() > 0) {
                // Descontar de múltiples lotes
                foreach ($item->lots as $saleItemLot) {
                    $lot = ProductLot::find($saleItemLot->product_lot_id);
                    
                    if ($lot) {
                        // Descontar cantidad
                        $lot->decrement('quantity', $saleItemLot->quantity);
                        $lot->refresh();
                        
                        // Desactivar lote si queda en 0
                        if ($lot->quantity <= 0) {
                            $lot->update(['is_active' => false]);
                            Log::info("✓ Lote {$lot->lot_number} desactivado automáticamente (quantity = 0)");
                        }
                        
                        // Registrar movimiento de stock
                        StockMovement::create([
                            'product_id' => $item->product_id,
                            'product_lot_id' => $lot->id,
                            'location_id' => $sale->location_id,
                            'type' => 'salida',
                            'quantity' => -$saleItemLot->quantity,
                            'reference_type' => 'App\Models\Sale',
                            'reference_id' => $sale->id,
                            'notes' => "Venta B2B #{$sale->id} - Lote: {$lot->lot_number}",
                        ]);
                        
                        Log::info("✓ Stock descontado: Producto {$item->product_id}, Lote {$lot->lot_number}, Cantidad: {$saleItemLot->quantity}, Stock restante: {$lot->quantity}");
                    }
                }
            } else {
                Log::warning("Item {$item->id} del pedido {$sale->id} no tiene lotes asignados en sale_item_lots");
            }
        }
        
        Log::info("=== Inventario actualizado para pedido #{$sale->id} ===");
    }
}
