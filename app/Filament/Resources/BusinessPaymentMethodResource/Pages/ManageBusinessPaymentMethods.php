<?php

namespace App\Filament\Resources\BusinessPaymentMethodResource\Pages;

use App\Filament\Resources\BusinessPaymentMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBusinessPaymentMethods extends ManageRecords
{
    protected static string $resource = BusinessPaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
