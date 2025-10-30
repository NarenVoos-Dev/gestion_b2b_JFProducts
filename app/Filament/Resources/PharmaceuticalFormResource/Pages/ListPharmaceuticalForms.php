<?php

namespace App\Filament\Resources\PharmaceuticalFormResource\Pages;

use App\Filament\Resources\PharmaceuticalFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPharmaceuticalForms extends ListRecords
{
    protected static string $resource = PharmaceuticalFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
