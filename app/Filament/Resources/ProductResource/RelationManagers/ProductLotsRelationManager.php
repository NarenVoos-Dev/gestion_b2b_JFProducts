<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use App\Models\Location; // <-- Importante

class ProductLotsRelationManager extends RelationManager
{
    protected static string $relationship = 'productLots';

    protected static ?string $title = 'Lotes de Inventario por Bodega';
    protected static ?string $label = 'Lote';
    protected static ?string $pluralLabel = 'Lotes';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('location_id')
                    ->label('Bodega / Sucursal')
                    // Usamos el modelo Location importado
                    ->options(Location::query()->where('business_id', auth()->user()->business_id)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('lot_number')
                    ->label('Número de Lote')
                    ->required(),
                DatePicker::make('expiration_date')
                    ->label('Fecha de Vencimiento')
                    ->required(),
                TextInput::make('quantity')
                    ->label('Cantidad Actual')
                    ->numeric()
                    ->required(),
                TextInput::make('cost')
                    ->label('Costo de Adquisición')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('stock_minimo')
                    ->label('Stock Mínimo')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('lot_number')
            ->columns([
                TextColumn::make('location.name')->label('Bodega')->searchable()->sortable(),
                TextColumn::make('lot_number')->label('N° Lote')->searchable()->sortable(),
                TextColumn::make('cost')->label('Costo')->money('cop')->sortable(),
                TextColumn::make('quantity')->label('Cantidad')->numeric()->sortable(),
                TextColumn::make('expiration_date')->label('Vencimiento')->date('d/m/Y')->sortable(),
            ])
            ->defaultSort('expiration_date', 'asc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),
            ]);
    }
}