<?php

namespace App\Filament\Resources\InventoryEntryResource\Pages;

use App\Filament\Resources\InventoryEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateInventoryEntry extends CreateRecord
{
    protected static string $resource = InventoryEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asegurar que todos los lotes se creen como activos
        if (isset($data['productLots'])) {
            foreach ($data['productLots'] as &$lot) {
                $lot['is_active'] = $lot['is_active'] ?? true;
                $lot['location_id'] = $data['location_id']; // Asignar la misma bodega
            }
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Ingreso de inventario creado exitosamente';
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->success()
            ->title('¡Ingreso registrado!')
            ->body('Los productos han sido agregados al inventario correctamente.')
            ->send();
    }
}
