<?php

namespace App\Filament\Widgets;

use App\Models\ProductLot;
use Filament\Widgets\Widget;

class ExpiredProductsWidget extends Widget
{
    protected static string $view = 'filament.widgets.expired-products';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 1;
    
    public function getViewData(): array
    {
        // Productos vencidos - solo lotes activos
        $expiredProducts = ProductLot::where('expiration_date', '<', now())
            ->where('quantity', '>', 0)
            ->where('is_active', true)
            ->whereHas('product', function($query) {
                $query->where('is_active', true);
            })
            ->with(['product'])
            ->orderBy('expiration_date', 'asc')
            ->get();
        
        // Productos por vencer - solo lotes activos
        $expiringSoon = ProductLot::whereBetween('expiration_date', [now(), now()->addDays(30)])
            ->where('quantity', '>', 0)
            ->where('is_active', true)
            ->whereHas('product', function($query) {
                $query->where('is_active', true);
            })
            ->with(['product'])
            ->orderBy('expiration_date', 'asc')
            ->get();
        
        return [
            'expiredProducts' => $expiredProducts,
            'expiringSoon' => $expiringSoon,
            'totalExpired' => $expiredProducts->count(),
            'totalExpiringSoon' => $expiringSoon->count(),
        ];
    }
}
