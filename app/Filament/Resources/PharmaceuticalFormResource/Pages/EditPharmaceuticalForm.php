<?php

namespace App\Filament\Resources\PharmaceuticalFormResource\Pages;

use App\Filament\Resources\PharmaceuticalFormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPharmaceuticalForm extends EditRecord
{
    protected static string $resource = PharmaceuticalFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
