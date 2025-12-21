<?php

namespace App\Filament\Resources\AccountReceivableResource\Pages;

use App\Filament\Resources\AccountReceivableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountReceivables extends ListRecords
{
    protected static string $resource = AccountReceivableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction deshabilitado - Las cuentas se crean automáticamente desde ventas
            // Actions\CreateAction::make()
            //     ->icon('heroicon-o-plus-circle'),
        ];
    }
}
