<?php

namespace App\Filament\Resources\ProductChannelResource\Pages;

use App\Filament\Resources\ProductChannelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductChannel extends EditRecord
{
    protected static string $resource = ProductChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
