<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductLotResource\Pages;
use App\Models\ProductLot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Filament\Imports\LotsImporter; // Debe apuntar a la nueva clase
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Actions\Action;



class ProductLotResource extends Resource
{
    protected static ?string $model = ProductLot::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?string $modelLabel = 'Lote de Producto';
    protected static ?string $pluralModelLabel = 'Gestión de Lotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')->label('Producto')
                    ->relationship('product', 'name')->required()->searchable()->preload(),
                Select::make('location_id')->label('Bodega / Sucursal')
                    ->relationship('location', 'name')->required()->searchable()->preload(),
                TextInput::make('lot_number')->label('Número de Lote')->required(),
                DatePicker::make('expiration_date')->label('Fecha de Vencimiento')->required(),
                TextInput::make('quantity')->label('Cantidad Actual')->numeric()->required(),
                TextInput::make('cost')->label('Costo de Adquisición')->numeric()->prefix('$'),
                TextInput::make('stock_minimo')->label('Stock Mínimo')->numeric()->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')->label('Producto')->searchable()->sortable(),
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
                Action::make('downloadTemplate')
                    ->label('Descargar Plantilla')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(route('admin.templates.download-lot')) // Apunta a la ruta que creamos
                    ->openUrlInNewTab(),
                    
                ImportAction::make()
                    ->label('Importar Lotes desde Excel')
                    ->importer(LotsImporter::class)
                    ->color('success') // Cambia el color del botón a verde
                    ->icon('heroicon-o-document-arrow-up'),
                
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductLots::route('/'),
            'create' => Pages\CreateProductLot::route('/create'),
            'edit' => Pages\EditProductLot::route('/{record}/edit'),
        ];
    }    
}