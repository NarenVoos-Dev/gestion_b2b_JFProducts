<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Filament\Resources\SaleResource\Widgets\SalesStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Nuevo Pedido')
                ->icon('heroicon-o-plus-circle')
                ->url(route('filament.admin.resources.sales.create-b2b'))
                ->color('success'),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            // SalesStatsOverview::class, // Temporalmente desactivado por error de null
        ];
    }
}
