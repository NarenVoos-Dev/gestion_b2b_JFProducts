<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';
    protected static ?string $navigationGroup = 'Catalogos'; // Movido a Catálogos
    protected static ?int $navigationSort = 31;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('business_id')->default(auth()->user()->business_id),
                
                Section::make('Información Principal')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre del Producto (Descripción)'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('sku')->label('SKU (Código Interno)')->maxLength(255)->unique(ignoreRecord: true),
                                TextInput::make('barcode')->label('Código de Barras')->maxLength(255),
                            ]),
                    ]),
                
                Section::make('Clasificación')
                    ->schema([
                        Select::make('category_id') // Grupo Farmacológico
                            ->relationship('category', 'name')
                            ->label('Grupo Farmacológico (Categoría)')
                            ->searchable()->preload()->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\Hidden::make('business_id')->default(auth()->user()->business_id)
                            ]),
                        Select::make('product_type_id')
                            ->relationship('productType', 'name')
                            ->label('Tipo de Producto')
                            ->required()->searchable()->preload(),
                        Select::make('product_channel_id')
                            ->relationship('productChannel', 'name')
                            ->label('Canal')
                            ->searchable()->preload(),
                    ])->columns(3),
                
                Section::make('Detalles Farmacéuticos')
                    ->schema([
                        Select::make('molecule_id')
                            ->label('Molécula')
                            ->relationship('molecule', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([ // Permite crear una molécula al vuelo
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre de la Molécula')
                                    ->required()
                                    ->unique(),
                            ]),
                        
                        
                        TextInput::make('concentration')->label('Concentración'),
                        Select::make('pharmaceutical_form_id')
                            ->relationship('pharmaceuticalForm', 'name')
                            ->label('Forma Farmacéutica')
                            ->searchable()->preload(),
                        //TextInput::make('commercial_presentation')->label('Presentación Comercial'),
                        Select::make('unit_of_measure_id')
                            ->relationship('unitOfMeasure', 'name')
                            ->required()->searchable()->preload()->label('Unidad de Medida Base / Presentacion Comercial'),
                        
                        Select::make('commercial_name_id')
                            ->label('Nombre Comercial')
                            ->relationship('commercialName', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([ // Permite crear un nombre comercial al vuelo
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre Comercial')
                                    ->required()
                                    ->unique(),
                            ]),

                        Select::make('laboratory_id')
                            ->label('Laboratorio')
                            ->relationship('laboratory', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([ // Permite crear un laboratorio al vuelo
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre del Laboratorio')
                                    ->required()
                                    ->unique(),
                            ]),
                    ])->columns(3),
                
                Section::make('Regulación y Precios')
                    ->schema([
                        TextInput::make('price')->label('Precio de Venta')->required()->numeric()->prefix('$'),
                        TextInput::make('price_regulated_reg')->label('Precio regulado regional')->required()->numeric()->prefix('$'),

                        TextInput::make('stock_minimo')
                            ->label('Stock Mínimo Total')
                            ->numeric()
                            ->default(0)
                            ->helperText('Alerta cuando el stock total (suma de todos los lotes) sea igual o menor a este valor.'),
                        TextInput::make('invima_registration')->label('Registro INVIMA'),
                        TextInput::make('cum')->label('CUM'),
                        TextInput::make('atc_code')->label('Código ATC'),
                        Grid::make(4)
                            ->schema([
                                Toggle::make('controlled')->label('Es Controlado'),
                                Toggle::make('cold_chain')->label('Cadena de Frío'),
                                Toggle::make('regulated')->label('Regulado'),
                                Toggle::make('is_active')->label('Producto Activo')->default(true),
                            ]),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('sku')->label('SKU')->searchable(),
                TextColumn::make('category.name')->label('Grupo')->badge()->searchable(),
                TextColumn::make('productType.name')->label('Tipo')->badge(),
                TextColumn::make('price')->money('cop')->sortable()->label('Precio'),
                
                // Columna de Stock Total (lee el accesor del modelo)
                TextColumn::make('total_stock')
                    ->label('Stock Total')
                    ->numeric()
                    ->sortable(),
                
                IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            // <<< CAMBIO: Apuntamos al nuevo RelationManager de Lotes >>>
            RelationManagers\ProductLotsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }    
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }
}