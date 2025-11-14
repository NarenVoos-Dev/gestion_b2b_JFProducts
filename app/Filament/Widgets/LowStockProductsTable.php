<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Product;
use App\Filament\Resources\ProductResource;

class LowStockProductsTable extends BaseWidget
{
    protected static ?int $sort = 3; // Orden en el dashboard
    protected int | string | array $columnSpan = '1';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    // Sumamos la cantidad de todos los lotes del producto
                    ->withSum('productLots', 'quantity')
                    // Solo mostramos productos donde el 'stock_minimo' es mayor a 0
                    ->where('stock_minimo', '>', 0)
                    // Usamos un filtro avanzado para comparar la suma con la columna
                    ->whereRaw(
                        '(SELECT SUM(quantity) FROM product_lots WHERE product_lots.product_id = products.id) <= products.stock_minimo'
                    )
            )
            ->heading('Productos con Bajo Stock Total')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->searchable(),

                // Esta columna ahora es la suma de todos los lotes
                Tables\Columns\TextColumn::make('product_lots_sum_quantity')
                    ->label('Stock Total Actual')
                    ->numeric()
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('stock_minimo')
                    ->label('Stock Mínimo')
                    ->numeric()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('stock_minimo')
                    ->label('Stock Mínimo')
                    ->numeric()
                    ->color('warning'),

            ])
            ->defaultSort('name', 'asc') // Ordenar por stock más bajo primero
            ->paginated([10, 25, 50])
            ->poll('10s'); // Actualizar cada 30 segundos
    }
}