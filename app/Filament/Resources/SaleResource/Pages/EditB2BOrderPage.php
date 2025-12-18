<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\Page;

class EditB2BOrderPage extends Page
{
    protected static string $resource = SaleResource::class;
    protected static string $view = 'filament.resources.sale-resource.pages.edit-b2b-order';
    
    public $saleId;
    
    public function mount($record): void
    {
        $this->saleId = $record;
    }
    
    public function getTitle(): string
    {
        return 'Editar Pedido B2B';
    }
}
