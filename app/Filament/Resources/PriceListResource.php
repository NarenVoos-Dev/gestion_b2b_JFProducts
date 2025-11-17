<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceListResource\Pages;
use App\Filament\Resources\PriceListResource\RelationManagers;
use App\Models\PriceList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Get;
use Filament\Support\Colors\Color;

class PriceListResource extends Resource
{
    protected static ?string $model = PriceList::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $modelLabel = 'Lista de Precios';
    protected static ?string $pluralModelLabel = 'Listas de Precios';
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('business_id')
                    ->default(auth()->user()->business_id),
                
                Section::make('Información de la Lista de Precios')
                    ->description('Configure una nueva lista con aumentos o descuentos sobre el precio base')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre de la Lista')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ej: Precio Mayorista, Precio Cliente VIP')
                                    ->columnSpan(2)
                                    ->autocomplete(false),
                            ]),
                    ]),
                
                Section::make('Configuración de Ajuste de Precio')
                    ->description('Defina el tipo y porcentaje de ajuste que se aplicará')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('type')
                                    ->label('Tipo de Ajuste')
                                    ->options([
                                        'markup' => ' Aumento de Precio (Markup)',
                                        'discount' => ' Descuento',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        // Limpiar el porcentaje cuando cambia el tipo
                                        $set('percentage', null);
                                    })
                                    ->helperText(fn (Get $get) => match ($get('type')) {
                                        'markup' => '💡 El precio final será mayor al precio base',
                                        'discount' => '💡 El precio final será menor al precio base',
                                        default => 'Seleccione el tipo de ajuste a aplicar',
                                    }),
                                
                                TextInput::make('percentage')
                                    ->label('Porcentaje de Ajuste')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->placeholder('Ej: 10')
                                    ->helperText(fn (Get $get) => match ($get('type')) {
                                        'markup' => 'El precio aumentará en este porcentaje',
                                        'discount' => 'El precio se reducirá en este porcentaje',
                                        default => 'Ingrese el porcentaje entre 0 y 100',
                                    })
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Get $get) {
                                        // Validar que no sea negativo
                                        if ($state < 0) {
                                            $set('percentage', 0);
                                        }
                                    }),
                            ]),
                        
                        // Ejemplo visual del cálculo
                        Grid::make(1)
                            ->schema([
                                Forms\Components\Placeholder::make('example')
                                    ->label('Ejemplo de Cálculo')
                                    ->content(function (Get $get) {
                                        $type = $get('type');
                                        $percentage = $get('percentage') ?? 0;
                                        
                                        if (!$type || !$percentage) {
                                            return '📝 Complete los campos para ver un ejemplo';
                                        }
                                        
                                        $basePrice = 100000; // Precio ejemplo
                                        
                                        if ($type === 'markup') {
                                            // Fórmula: Precio Base / (1 - % Rentabilidad)
                                            if ($percentage >= 100) {
                                                return '⚠️ El porcentaje debe ser menor a 100% para calcular el markup';
                                            }
                                            $finalPrice = $basePrice / (1 - ($percentage / 100));
                                            $diff = $finalPrice - $basePrice;
                                            return "💰 Precio Base: $" . number_format($basePrice, 0, ',', '.') . 
                                                   " → Precio Final: $" . number_format($finalPrice, 0, ',', '.') . 
                                                   " (+$" . number_format($diff, 0, ',', '.') . ")" .
                                                   " | Fórmula: Base / (1 - " . $percentage . "%)";
                                        } else {
                                            $finalPrice = $basePrice * (1 - ($percentage / 100));
                                            $diff = $basePrice - $finalPrice;
                                            return "💰 Precio Base: $" . number_format($basePrice, 0, ',', '.') . 
                                                   " → Precio Final: $" . number_format($finalPrice, 0, ',', '.') . 
                                                   " (-$" . number_format($diff, 0, ',', '.') . ")";
                                        }
                                    })
                                    ->extraAttributes([
                                        'class' => 'text-sm bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700',
                                    ]),
                            ]),
                    ]),
                
                /*Section::make('Notas Adicionales')
                    ->description('Información adicional sobre esta lista de precios (opcional)')
                    ->icon('heroicon-o-document-text')
                    ->collapsed()
                    ->schema([
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->placeholder('Ej: Lista especial para clientes corporativos con compras mayores a $1.000.000')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),*/
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre de la Lista')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-tag')
                    ->copyable()
                    ->copyMessage('Nombre copiado')
                    ->tooltip('Clic para copiar'),
                
                BadgeColumn::make('type')
                    ->label('Tipo de Ajuste')
                    ->formatStateUsing(fn (string $state): string => $state === 'markup' ? '📈 Aumento' : '📉 Descuento')
                    ->color(fn (string $state): string => $state === 'markup' ? 'warning' : 'success')
                    ->size('md'),
                
                TextColumn::make('percentage')
                    ->label('Porcentaje')
                    ->suffix('%')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($record) => $record->type === 'markup' ? 'warning' : 'success')
                    ->formatStateUsing(fn ($state, $record) => 
                        ($record->type === 'markup' ? '+' : '-') . number_format($state, 2) . '%'
                    ),
                
                TextColumn::make('example_calculation')
                    ->label('Ejemplo (Base: $100.000)')
                    ->state(function ($record) {
                        $base = 100000;
                        if ($record->type === 'markup') {
                           if ($record->percentage >= 100) {
                                return 'N/A';
                            }
                            $final = $base / (1 - ($record->percentage / 100));
                        } else {
                            $final = $base * (1 - ($record->percentage / 100));
                        }
                        return '$' . number_format($final, 0, ',', '.');
                    })
                    ->color('primary')
                    ->weight('medium')
                    ->toggleable(),
                
                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo de Ajuste')
                    ->options([
                        'markup' => 'Aumento',
                        'discount' => 'Descuento',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Lista de Precios')
                    ->modalDescription('¿Está seguro de eliminar esta lista? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No hay listas de precios')
            ->emptyStateDescription('Crea tu primera lista de precios para aplicar ajustes automáticos.')
            ->emptyStateIcon('heroicon-o-tag')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crear Primera Lista')
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
            'index' => Pages\ListPriceLists::route('/'),
            'create' => Pages\CreatePriceList::route('/create'),
            'edit' => Pages\EditPriceList::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }
}