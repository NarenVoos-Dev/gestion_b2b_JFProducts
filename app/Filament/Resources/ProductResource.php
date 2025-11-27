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
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';
    protected static ?string $navigationGroup = 'Catalogos';
    protected static ?int $navigationSort = 31;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('business_id')
                    ->default(auth()->user()->business_id),
                
                Tabs::make('Información del Producto')
                    ->tabs([
                        // TAB 1: Información General
                        Tabs\Tab::make('General')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Nombre del Producto')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpan(2)
                                                    ->placeholder('Ej: Paracetamol 500mg'),
                                                
                                                Select::make('product_type_id')
                                                    ->relationship('productType', 'name')
                                                    ->label('Tipo')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->columnSpan(1),
                                            ]),
                                        
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('sku')
                                                    ->label('SKU')
                                                    ->maxLength(255)
                                                    ->unique(ignoreRecord: true)
                                                    ->placeholder('Código interno'),
                                                
                                                TextInput::make('barcode')
                                                    ->label('Código de Barras')
                                                    ->maxLength(255)
                                                    ->placeholder('EAN-13 / UPC'),
                                                
                                                Select::make('product_channel_id')
                                                    ->relationship('productChannel', 'name')
                                                    ->label('Canal de Venta')
                                                    ->searchable()
                                                    ->preload(),
                                            ]),
                                    ])
                                    ->columns(1),
                            ]),
                        
                        // TAB 2: Clasificación Farmacéutica
                        Tabs\Tab::make('Farmacéutica')
                            ->icon('heroicon-o-beaker')
                            ->schema([
                                Section::make('Clasificación')
                                    ->schema([
                                        Select::make('category_id')
                                            ->relationship('category', 'name')
                                            ->label('Grupo Farmacológico')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')->required(),
                                                Forms\Components\Hidden::make('business_id')->default(auth()->user()->business_id)
                                            ]),
                                    ])
                                    ->columns(1),
                                
                                Section::make('Composición')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('molecule_id')
                                                    ->label('Principio Activo / Molécula')
                                                    ->relationship('molecule', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->createOptionForm([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Nombre de la Molécula')
                                                            ->required()
                                                            ->unique(),
                                                    ]),
                                                
                                                TextInput::make('concentration')
                                                    ->label('Concentración')
                                                    ->placeholder('Ej: 500mg, 10ml'),
                                            ]),
                                    ])
                                    ->columns(1),
                                
                                Section::make('Presentación')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Select::make('pharmaceutical_form_id')
                                                    ->relationship('pharmaceuticalForm', 'name')
                                                    ->label('Forma Farmacéutica')
                                                    ->searchable()
                                                    ->preload(),
                                                
                                                Select::make('unit_of_measure_id')
                                                    ->relationship('unitOfMeasure', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->label('Unidad / Presentación'),
                                                
                                                Select::make('commercial_name_id')
                                                    ->label('Nombre Comercial')
                                                    ->relationship('commercialName', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->createOptionForm([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Nombre Comercial')
                                                            ->required()
                                                            ->unique(),
                                                    ]),
                                            ]),
                                    ])
                                    ->columns(1),
                                
                                Section::make('Fabricante')
                                    ->schema([
                                        Select::make('laboratory_id')
                                            ->label('Laboratorio')
                                            ->relationship('laboratory', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nombre del Laboratorio')
                                                    ->required()
                                                    ->unique(),
                                            ]),
                                    ])
                                    ->columns(1),
                            ]),
                        
                        // TAB 3: Precios y Stock
                        Tabs\Tab::make('Precios & Stock')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Section::make('Precios')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('price_regulated_reg')
                                                    ->label('Precio Regulado Regional')
                                                    ->required()
                                                    ->numeric()
                                                    ->prefix('$')
                                                    ->placeholder('0.00'),
                                                
                                                TextInput::make('stock_minimo')
                                                    ->label('Stock Mínimo (Alerta)')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->suffix('unidades')
                                                    ->helperText('Alerta cuando el stock total sea igual o menor.'),
                                            ]),
                                    ])
                                    ->columns(1),
                                
                                Section::make('Impuestos')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('has_tax')
                                                    ->label('Aplica IVA')
                                                    ->default(false)
                                                    ->live()
                                                    ->inline(false),
                                                
                                                TextInput::make('tax_rate')
                                                    ->label('Porcentaje de IVA')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->required()
                                                    ->visible(fn (Get $get): bool => $get('has_tax'))
                                                    ->suffix('%')
                                                    ->placeholder('19'),
                                            ]),
                                    ])
                                    ->columns(1),
                            ]),
                        
                        // TAB 4: Regulación
                        Tabs\Tab::make('Regulación')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Section::make('Registros Sanitarios')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('invima_registration')
                                                    ->label('Registro INVIMA')
                                                    ->placeholder('INVIMA-XXXXXX'),
                                                
                                                TextInput::make('cum')
                                                    ->label('CUM')
                                                    ->placeholder('Código único de medicamento'),
                                                
                                                TextInput::make('atc_code')
                                                    ->label('Código ATC')
                                                    ->placeholder('Clasificación ATC'),
                                            ]),
                                    ])
                                    ->columns(1),
                                
                                Section::make('Características Especiales')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                Toggle::make('controlled')
                                                    ->label('Medicamento Controlado')
                                                    ->inline(false),
                                                
                                                Toggle::make('cold_chain')
                                                    ->label('Cadena de Frío')
                                                    ->inline(false),
                                                
                                                Toggle::make('regulated')
                                                    ->label('Precio Regulado')
                                                    ->inline(false),
                                                
                                                Toggle::make('is_active')
                                                    ->label('Producto Activo')
                                                    ->default(true)
                                                    ->inline(false),
                                            ]),
                                    ])
                                    ->columns(1)
                                    ->description('Marca las características especiales que aplican a este producto'),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(40),
                
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable()
                    ->copyable()
                    ->copyMessage('SKU copiado')
                    ->badge()
                    ->color('gray'),
                
                TextColumn::make('commercialName.name')
                    ->label('Nombre Comercial')
                    ->searchable()
                    ->toggleable()
                    ->limit(30)
                    ->placeholder('Sin nombre comercial'),
                
                TextColumn::make('category.name')
                    ->label('Grupo Farmacológico')
                    ->badge()
                    ->searchable()
                    ->toggleable()
                    ->color('info'),
                
                TextColumn::make('productType.name')
                    ->label('Tipo')
                    ->badge()
                    ->toggleable()
                    ->color('success'),
                
                TextColumn::make('price_regulated_reg')
                    ->label('Precio')
                    ->money('cop')
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                
                TextColumn::make('total_stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state, $record) => $state <= $record->stock_minimo ? 'danger' : 'success')
                    ->icon(fn ($state, $record) => $state <= $record->stock_minimo ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle'),
                
                IconColumn::make('controlled')
                    ->label('Control.')
                    ->boolean()
                    ->toggleable()
                    ->tooltip('Medicamento Controlado'),
                
                IconColumn::make('cold_chain')
                    ->label('Frío')
                    ->boolean()
                    ->toggleable()
                    ->tooltip('Cadena de Frío'),
                
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Grupo Farmacológico')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('productType')
                    ->label('Tipo de Producto')
                    ->relationship('productType', 'name'),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
                
                Tables\Filters\TernaryFilter::make('controlled')
                    ->label('Controlados')
                    ->placeholder('Todos')
                    ->trueLabel('Solo controlados')
                    ->falseLabel('No controlados'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar seleccionados')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('name', 'asc')
            ->striped()
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession();
    }
    
    public static function getRelations(): array
    {
        return [
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
        return parent::getEloquentQuery()
            ->where('business_id', auth()->user()->business_id)
            ->with([
                'category',
                'commercialName',
                'productType',
                'laboratory',
                'molecule',
                'unitOfMeasure',
                'pharmaceuticalForm',
                'productChannel',
                'productLots', // Para calcular total_stock
            ]);
    }
}