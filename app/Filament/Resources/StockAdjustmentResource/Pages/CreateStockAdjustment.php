<?php

namespace App\Filament\Resources\StockAdjustmentResource\Pages;

use App\Filament\Resources\StockAdjustmentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

use App\Models\StockMovement;
use App\Models\Inventory;
use Filament\Notifications\Notification;

class CreateStockAdjustment extends CreateRecord
{
    protected static string $resource = StockAdjustmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            
            $quantity = (float)$data['quantity'];
            
            // 1. Encontrar el lote específico
            $lot = ProductLot::findOrFail($data['product_lot_id']);
            
            // 2. Validar si es una salida y hay suficiente stock
            if ($data['type'] === 'salida' && $lot->quantity < $quantity) {
                Notification::make()
                    ->title('Error de Stock')
                    ->body("No hay suficiente stock en el lote {$lot->lot_number}. Stock actual: {$lot->quantity}.")
                    ->danger()
                    ->send();
                $this->halt();
            }

            // 3. Crear el registro del ajuste
            $adjustment = static::getModel()::create($data);

            // 4. Actualizar el stock del LOTE
            if ($data['type'] === 'entrada') {
                $lot->increment('quantity', $quantity);
            } else {
                $lot->decrement('quantity', $quantity);
            }
            
            // 5. Registrar el movimiento para auditoría
            StockMovement::create([
                'product_id' => $data['product_id'],
                'type' => $data['type'],
                'quantity' => $quantity,
                'source_type' => get_class($adjustment),
                'source_id' => $adjustment->id,
            ]);

            return $adjustment;
        });
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return '¡Ajuste de inventario registrado con éxito!';
    }
}