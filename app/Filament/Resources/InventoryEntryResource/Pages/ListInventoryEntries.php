<?php

namespace App\Filament\Resources\InventoryEntryResource\Pages;

use App\Filament\Resources\InventoryEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventoryEntries extends ListRecords
{
    protected static string $resource = InventoryEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
