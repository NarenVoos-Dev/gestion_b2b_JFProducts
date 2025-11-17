<?php

namespace App\Filament\Resources\ProductLotResource\Pages;

use App\Filament\Resources\ProductLotResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductLot extends EditRecord
{
    protected static string $resource = ProductLotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Lote editado exitosamente';
    }
     protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        // Si hay un error de duplicado en la base de datos
        if (str_contains($exception->getMessage(), 'Duplicate entry')) {
            Notification::make()
                ->danger()
                ->title('Lote duplicado')
                ->body('Ya existe un lote con este número para el producto y bodega seleccionados.')
                ->persistent()
                ->send();
        }

        parent::onValidationError($exception);
    }
}
