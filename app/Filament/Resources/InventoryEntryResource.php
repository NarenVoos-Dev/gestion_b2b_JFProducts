<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryEntryResource\Pages;
use App\Filament\Resources\InventoryEntryResource\RelationManagers;
use App\Models\InventoryEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use App\Models\Location;
use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Get;
use Filament\Support\Colors\Color;

class InventoryEntryResource extends Resource
{
    protected static ?string $model = InventoryEntry::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $modelLabel = 'Ingreso de Inventario';
    protected static ?string $pluralModelLabel = 'Ingresos de Inventario';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('business_id')
                    ->default(auth()->user()->business_id),
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),

                Section::make('📋 Información General del Ingreso')
                    ->description('Datos principales de la entrada de inventario')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('location_id')
                                ->label('Bodega de Destino')
                                ->options(Location::query()
                                    ->where('business_id', auth()->user()->business_id)
                                    ->pluck('name', 'id'))
                                ->required()
                                ->native(false)
                                ->searchable()
                                ->preload()
                                ->live()
                                ->helperText('Selecciona la bodega donde ingresarán los productos'),
                            
                            DatePicker::make('entry_date')
                                ->label('Fecha de Ingreso')
                                ->default(now())
                                ->required()
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->maxDate(now())
                                ->helperText('Fecha en que se realiza el ingreso'),
                            
                            Select::make('city')
                                ->label('Ciudad de Origen')
                                ->options([
                                    'Barranquilla' => '🌴 Barranquilla',
                                    'Bogotá' => '🏛️ Bogotá',
                                    'Cali' => '🌺 Cali',
                                    'Medellín' => '🏔️ Medellín',
                                    'Cartagena' => '🏖️ Cartagena',
                                    'Otro' => '📍 Otra Ciudad',
                                ])
                                ->searchable()
                                ->native(false)
                                ->placeholder('Selecciona una ciudad'),
                        ]),
                        
                        Grid::make(1)->schema([
                            TextInput::make('reference')
                                ->label('Referencia del Documento')
                                ->placeholder('Ej: Factura #12345, Guía #ABC-001, Orden de Compra #OC-789')
                                ->maxLength(255)
                                ->prefixIcon('heroicon-o-document-text')
                                ->columnSpanFull(),
                            
                            Textarea::make('notes')
                                ->label('Observaciones Adicionales')
                                ->placeholder('Agrega notas relevantes sobre este ingreso...')
                                ->rows(3)
                                ->maxLength(1000)
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed(),

                Section::make('📦 Detalle de Productos y Lotes')
                    ->description('Agrega los productos que ingresan con sus respectivos lotes y cantidades')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Repeater::make('productLots')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Grid::make(12)->schema([
                                    Select::make('product_id')
                                        ->label('Producto')
                                        ->options(function () {
                                            return Product::query()
                                                ->where('business_id', auth()->user()->business_id)
                                                ->where('is_active', true)
                                                ->with(['category', 'commercialName'])
                                                ->get()
                                                ->mapWithKeys(function ($product) {
                                                    $commercial = $product->commercialName ? ' - ' . $product->commercialName->name : '';
                                                    $category = $product->category ? ' (' . $product->category->name . ')' : '';
                                                    return [$product->id => $product->name . $commercial . $category];
                                                });
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->native(false)
                                        ->live()
                                        ->columnSpan(4),
                                        
                                    
                                    TextInput::make('lot_number')
                                        ->label('Número de Lote')
                                        ->required()
                                        ->maxLength(100)
                                        ->placeholder('Ej: LOT-2024-001')
                                        ->columnSpan(2),
                                    
                                    DatePicker::make('expiration_date')
                                        ->label('Fecha de Vencimiento')
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->minDate(now())
                                        ->columnSpan(2),
                                    
                                    TextInput::make('quantity')
                                        ->label('Cantidad')
                                        ->numeric()
                                        ->required()
                                        ->default(1)
                                        ->minValue(1)
                                        ->step(1)
                                        ->suffix('und')
                                        ->columnSpan(2),
                                    
                                    TextInput::make('cost')
                                        ->label('Costo Unitario')
                                        ->numeric()
                                        ->required()
                                        ->default(0)
                                        ->minValue(0)
                                        ->prefix('$')
                                        ->live()
                                        ->columnSpan(2)
                                        ->extraInputAttributes(['step' => '0.01']),
                                    
                                    /*Placeholder::make('subtotal')
                                        ->label('Subtotal')
                                        ->content(function (Get $get) {
                                            $quantity = floatval($get('quantity') ?? 0);
                                            $cost = floatval($get('cost') ?? 0);
                                            $subtotal = $quantity * $cost;
                                            return '$' . number_format($subtotal, 2, ',', '.');
                                        })
                                        ->columnSpan(2),*/
                                ]),
                            ])
                            ->columns(1)
                            ->columnSpanFull()
                            ->cloneable()
                            ->reorderable()
                            ->addActionLabel('➕ Agregar otro producto/lote')
                            ->defaultItems(1)
                            ->deleteAction(
                                fn ($action) => $action->requiresConfirmation()
                            )
                            ->itemLabel(fn (array $state): ?string => 
                                $state['product_id'] 
                                    ? Product::find($state['product_id'])?->name . ' - Lote: ' . ($state['lot_number'] ?? 'N/A')
                                    : 'Producto sin configurar'
                            )
                            ->collapsed()
                            ->collapsible(),
                        
                        /*Placeholder::make('total')
                            ->label('💰 Total General del Ingreso')
                            ->content(function (Get $get) {
                                $productLots = $get('productLots') ?? [];
                                $total = 0;
                                foreach ($productLots as $lot) {
                                    $quantity = floatval($lot['quantity'] ?? 0);
                                    $cost = floatval($lot['cost'] ?? 0);
                                    $total += $quantity * $cost;
                                }
                                return '$' . number_format($total, 2, ',', '.');
                            })
                            ->extraAttributes([
                                'class' => 'text-xl font-bold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950 p-4 rounded-lg border-2 border-primary-200 dark:border-primary-800',
                            ]),*/
                    ])
                    ->collapsible()
                    ->persistCollapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => '#' . str_pad($state, 5, '0', STR_PAD_LEFT)),
                
                TextColumn::make('entry_date')
                    ->label('Fecha de Ingreso')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->color('gray'),
                
                TextColumn::make('location.name')
                    ->label('Bodega')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-building-storefront'),
                
                TextColumn::make('reference')
                    ->label('Referencia')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($state) => $state)
                    ->icon('heroicon-o-document-text')
                    ->placeholder('Sin referencia')
                    ->copyable()
                    ->copyMessage('Referencia copiada'),
                
                TextColumn::make('city')
                    ->label('Ciudad')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'Barranquilla' => 'warning',
                        'Bogotá' => 'success',
                        'Cali' => 'danger',
                        'Medellín' => 'info',
                        default => 'gray',
                    })
                    ->toggleable(),
                
                TextColumn::make('productLots_count')
                    ->label('Total Items')
                    ->counts('productLots')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-cube')
                    ->suffix(' productos'),
                
                TextColumn::make('user.name')
                    ->label('Registrado por')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-user'),
                
                TextColumn::make('created_at')
                    ->label('Fecha de Registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('location_id')
                    ->label('Filtrar por Bodega')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('city')
                    ->label('Filtrar por Ciudad')
                    ->options([
                        'Barranquilla' => 'Barranquilla',
                        'Bogotá' => 'Bogotá',
                        'Cali' => 'Cali',
                        'Medellín' => 'Medellín',
                        'Cartagena' => 'Cartagena',
                        'Otro' => 'Otra',
                    ]),
                
                Tables\Filters\Filter::make('entry_date')
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Desde')
                            ->native(false),
                        Forms\Components\DatePicker::make('hasta')
                            ->label('Hasta')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('entry_date', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('entry_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['desde'] ?? null) {
                            $indicators['desde'] = 'Desde: ' . \Carbon\Carbon::parse($data['desde'])->format('d/m/Y');
                        }
                        if ($data['hasta'] ?? null) {
                            $indicators['hasta'] = 'Hasta: ' . \Carbon\Carbon::parse($data['hasta'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Ingreso')
                    ->modalDescription('¿Está seguro? Esta acción eliminará el ingreso y todos sus productos asociados.')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->striped()
            ->emptyStateHeading('No hay ingresos registrados')
            ->emptyStateDescription('Crea tu primer ingreso de inventario para comenzar a gestionar tus productos.')
            ->emptyStateIcon('heroicon-o-arrow-down-tray')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crear Primer Ingreso')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryEntries::route('/'),
            'create' => Pages\CreateInventoryEntry::route('/create'),
            'edit' => Pages\EditInventoryEntry::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }
}