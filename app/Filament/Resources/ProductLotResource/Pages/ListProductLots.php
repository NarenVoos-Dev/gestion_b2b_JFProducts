<?php

namespace App\Filament\Resources\ProductLotResource\Pages;

use App\Filament\Resources\ProductLotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductLots extends ListRecords
{
    protected static string $resource = ProductLotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
