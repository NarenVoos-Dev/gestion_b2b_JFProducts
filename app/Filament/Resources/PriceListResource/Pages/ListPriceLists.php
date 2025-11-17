<?php

namespace App\Filament\Resources\PriceListResource\Pages;

use App\Filament\Resources\PriceListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab; 
use Illuminate\Database\Eloquent\Builder;

class ListPriceLists extends ListRecords
{
    protected static string $resource = PriceListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todas las Listas'),

            'markup' => Tab::make('Listas de Aumento (Precio)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'markup')),

            'discount' => Tab::make('Listas de Descuento')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'discount')),
        ];
    }
}
