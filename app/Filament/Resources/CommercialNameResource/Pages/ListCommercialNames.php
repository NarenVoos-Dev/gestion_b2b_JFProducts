<?php

namespace App\Filament\Resources\CommercialNameResource\Pages;

use App\Filament\Resources\CommercialNameResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCommercialNames extends ListRecords
{
    protected static string $resource = CommercialNameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
