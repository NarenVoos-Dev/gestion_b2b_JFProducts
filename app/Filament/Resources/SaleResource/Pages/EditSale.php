<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Forms;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->source === 'pos'),
        ];
    }
    
    // Prevenir edición de pedidos B2B facturados o finalizados
    protected function beforeFill(): void
    {
        if ($this->record->source === 'b2b' && !in_array($this->record->status, ['Pendiente', 'Separación'])) {
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('Edición no disponible')
                ->body('Solo se pueden editar pedidos B2B en estado Pendiente o Separación.')
                ->persistent()
                ->send();
                
            $this->redirect(SaleResource::getUrl('view', ['record' => $this->record]));
        }
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['items'] = $this->record->items->toArray();
        return $data;
    }
    
    public function form(Forms\Form $form): Forms\Form
    {
        // Formulario unificado para B2B (crear y editar) con asignación de lotes
        if ($this->record->source === 'b2b') {
            return $form->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Cliente')
                            ->relationship('client', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn ($context) => $context === 'edit'),
                        Forms\Components\DateTimePicker::make('date')
                            ->label('Fecha')
                            ->default(now())
                            ->disabled(fn ($context) => $context === 'edit'),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'Pendiente' => 'Pendiente',
                                'Separación' => 'Separación',
                            ])
                            ->default('Pendiente')
                            ->disabled(fn ($context) => $context === 'edit'),
                    ]),
                    
                Forms\Components\Textarea::make('notes')
                    ->label('Notas del Pedido')
                    ->rows(2)
                    ->columnSpanFull(),
                    
                Forms\Components\Section::make('Items del Pedido')
                    ->description('Agrega productos y asigna lotes directamente')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->label('Producto')
                                            ->relationship('product', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                if ($state) {
                                                    $product = \App\Models\Product::find($state);
                                                    if ($product) {
                                                        $set('price', $product->price);
                                                        $set('unit_of_measure_id', $product->unit_of_measure_id);
                                                        $set('tax_rate', $product->tax_rate ?? 0);
                                                    }
                                                }
                                            })
                                            ->columnSpan(2),
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Cantidad Total')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->reactive()
                                            ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => 
                                                SaleResource::updateTotals($get, $set)
                                            ),
                                        Forms\Components\Select::make('unit_of_measure_id')
                                            ->label('Unidad')
                                            ->relationship('unitOfMeasure', 'name')
                                            ->required()
                                            ->disabled(),
                                    ]),
                                    
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('price')
                                            ->label('Precio Unitario')
                                            ->numeric()
                                            ->required()
                                            ->prefix('$')
                                            ->reactive()
                                            ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => 
                                                SaleResource::updateTotals($get, $set)
                                            ),
                                        Forms\Components\Placeholder::make('item_total')
                                            ->label('Total Item')
                                            ->content(function (Forms\Get $get) {
                                                $quantity = (float)($get('quantity') ?? 0);
                                                $price = (float)($get('price') ?? 0);
                                                $total = $quantity * $price;
                                                return '$' . number_format($total, 2);
                                            }),
                                    ]),
                                    
                                // Asignación de lotes inline
                                Forms\Components\Section::make('Lotes Asignados')
                                    ->description('Asigna uno o varios lotes para este item')
                                    ->schema([
                                        Forms\Components\Repeater::make('lots')
                                            ->relationship('lots')
                                            ->schema([
                                                Forms\Components\Select::make('product_lot_id')
                                                    ->label('Lote')
                                                    ->options(function (Forms\Get $get) {
                                                        $productId = $get('../../product_id');
                                                        if (!$productId) return [];
                                                        
                                                        return \App\Models\ProductLot::where('product_id', $productId)
                                                            ->where('is_active', true)
                                                            ->where('quantity', '>', 0)
                                                            ->get()
                                                            ->mapWithKeys(function ($lot) {
                                                                return [
                                                                    $lot->id => "{$lot->lot_number} (Stock: {$lot->quantity}, Vence: {$lot->expiration_date->format('d/m/Y')})"
                                                                ];
                                                            })
                                                            ->toArray();
                                                    })
                                                    ->required()
                                                    ->searchable()
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                        if ($state) {
                                                            $lot = \App\Models\ProductLot::find($state);
                                                            if ($lot) {
                                                                $set('lot_number', $lot->lot_number);
                                                                $set('expiration_date', $lot->expiration_date);
                                                            }
                                                        }
                                                    }),
                                                Forms\Components\TextInput::make('quantity')
                                                    ->label('Cantidad')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(1),
                                                Forms\Components\Hidden::make('lot_number'),
                                                Forms\Components\Hidden::make('expiration_date'),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(0)
                                            ->addActionLabel('Agregar Lote')
                                            ->collapsible()
                                            ->collapsed(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Agregar Producto')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['product_id']) 
                                    ? \App\Models\Product::find($state['product_id'])?->name 
                                    : null
                            ),
                    ]),
                    
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('tax')
                            ->label('IVA')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'font-bold']),
                    ]),
            ]);
        }
        
        // Formulario original para POS
        return parent::form($form);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Para pedidos B2B, Filament maneja automáticamente las relaciones (items y lots)
        if ($record->source === 'b2b') {
            return DB::transaction(function () use ($record, $data) {
                // Actualizar la venta
                $record->update($data);
                
                \Filament\Notifications\Notification::make()
                    ->success()
                    ->title('Pedido Actualizado')
                    ->body('El pedido y sus lotes han sido actualizados correctamente.')
                    ->send();
                
                return $record;
            });
        }
        
        // Lógica para pedidos POS (original) - No implementada para simplificar
        throw new \Exception("La edición de pedidos POS no está disponible desde esta pantalla.");
    }
}
