<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockAdjustmentResource\Pages;
use App\Models\StockAdjustment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductLot;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = \App\Models\StockAdjustment::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    // Lo agrupamos en la sección de Inventario
    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?int $navigationSort = 15;
    protected static ?string $modelLabel = 'Ajuste de Inventario';
    protected static ?string $pluralModelLabel = 'Ajustes de Inventario';


    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Hidden::make('business_id')->default(auth()->user()->business_id),
            
            Select::make('location_id')
                ->label('Bodega / Sucursal')
                ->options(Location::query()->where('business_id', auth()->user()->business_id)->pluck('name', 'id'))
                ->live() // Hace que el formulario sea reactivo
                ->afterStateUpdated(fn (Set $set) => $set('product_id', null))
                ->required(),

            Select::make('product_id')
                ->label('Producto')
                ->options(function (Get $get) {
                    // Muestra solo productos que tienen lotes en la bodega seleccionada
                    if (!$get('location_id')) {
                        return [];
                    }
                    return Product::whereHas('productLots', fn ($q) => $q->where('location_id', $get('location_id')))
                        ->pluck('name', 'id');
                })
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('product_lot_id', null))
                ->searchable()
                ->required(),

            Select::make('product_lot_id')
                ->label('Lote Específico')
                ->options(function (Get $get) {
                    // Muestra solo los lotes del producto y la bodega seleccionados
                    if (!$get('product_id') || !$get('location_id')) {
                        return [];
                    }
                    return ProductLot::where('product_id', $get('product_id'))
                        ->where('location_id', $get('location_id'))
                        ->get()
                        ->mapWithKeys(fn ($lot) => [$lot->id => "Lote: {$lot->lot_number} (Vence: {$lot->expiration_date->format('d/m/Y')}) - Stock: {$lot->quantity}"])
                        ->toArray();
                })
                ->live()
                ->required()
                ->searchable(),

            Forms\Components\Select::make('type')
                ->label('Tipo de Ajuste')
                ->options(['entrada' => 'Entrada (Sumar)', 'salida' => 'Salida (Restar)'])
                ->required(),

            Forms\Components\TextInput::make('quantity')
                ->label('Cantidad a Ajustar')
                ->helperText('La cantidad que se sumará o restará del lote.')
                ->required()
                ->numeric(),
            
            Forms\Components\Textarea::make('reason')
                ->label('Motivo del Ajuste')
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label('Producto')->searchable(),
                Tables\Columns\TextColumn::make('type')->label('Tipo')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'entrada' => 'success',
                        'salida' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('quantity')->label('Cantidad')->numeric(),
                Tables\Columns\TextColumn::make('reason')->label('Motivo')->limit(50),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockAdjustments::route('/'),
            'create' => Pages\CreateStockAdjustment::route('/create'),
            // Los ajustes no se deberían editar para mantener la integridad de los movimientos.
            // 'edit' => Pages\EditStockAdjustment::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }
}