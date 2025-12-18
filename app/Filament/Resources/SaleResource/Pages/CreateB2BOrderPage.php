<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\Page;

class CreateB2BOrderPage extends Page
{
    protected static string $resource = SaleResource::class;

    protected static string $view = 'filament.resources.sale-resource.pages.create-b2b-order';
    
    protected static ?string $title = 'Crear Pedido B2B';
    
    protected static ?string $navigationLabel = 'Crear Pedido B2B';
}
