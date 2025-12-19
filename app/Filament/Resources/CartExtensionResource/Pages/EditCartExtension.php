<?php

namespace App\Filament\Resources\CartExtensionResource\Pages;

use App\Filament\Resources\CartExtensionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCartExtension extends EditRecord
{
    protected static string $resource = CartExtensionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
