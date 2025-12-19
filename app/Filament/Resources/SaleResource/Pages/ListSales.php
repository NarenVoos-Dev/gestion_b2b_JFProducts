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
    
    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make('Todos'),
            'pendiente' => \Filament\Resources\Components\Tab::make('Pendientes')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'Pendiente'))
                ->badge(\App\Models\Sale::where('status', 'Pendiente')->count()),
            'separacion' => \Filament\Resources\Components\Tab::make('Separación')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'Separacion'))
                ->badge(\App\Models\Sale::where('status', 'Separacion')->count()),
            'facturado' => \Filament\Resources\Components\Tab::make('Facturados')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'Facturado'))
                ->badge(\App\Models\Sale::where('status', 'Facturado')->count()),
            'finalizado' => \Filament\Resources\Components\Tab::make('Finalizados')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'Finalizado'))
                ->badge(\App\Models\Sale::where('status', 'Finalizado')->count()),
            'cancelado' => \Filament\Resources\Components\Tab::make('Cancelados')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'Cancelado'))
                ->badge(\App\Models\Sale::where('status', 'Cancelado')->count()),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            // SalesStatsOverview::class, // Temporalmente desactivado por error de null
        ];
    }
}
