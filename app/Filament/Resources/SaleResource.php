<?php
namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Sale;
use App\Models\Location; 
use App\Models\Inventory;
use App\Models\Product; 
use Barryvdh\DomPDF\Facade\Pdf;

use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;

class SaleResource extends Resource
{
    // Usamos el nombre de clase completamente calificado para evitar ambigüedades.
    protected static ?string $model = \App\Models\Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?int $navigationSort = 11;

    protected static ?string $modelLabel = 'Pedido de Venta';
    protected static ?string $pluralModelLabel = 'Pedidos de Venta';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // CAMBIO: Se re-introduce el Wizard
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Cliente y Fecha')
                        ->schema([
                            Forms\Components\Hidden::make('business_id')->default(auth()->user()->business_id),
                            Forms\Components\Select::make('client_id')
                                ->label('Cliente')
                                ->options(\App\Models\Client::query()->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required()
                                // CAMBIO: Se añade la capacidad de crear un cliente al vuelo
                                ->createOptionForm([
                                    Forms\Components\Hidden::make('business_id')->default(auth()->user()->business_id),
                                    Forms\Components\TextInput::make('name')->label('Nombre del Cliente')->required(),
                                    Forms\Components\TextInput::make('document')->label('Documento'),
                                    Forms\Components\TextInput::make('phone')->label('Teléfono'),
                                ])->createOptionUsing(function (array $data): int {
                                    return \App\Models\Client::create($data)->id;
                                }),
                            Forms\Components\DatePicker::make('date')->label('Fecha de la Venta')->required()->default(now()),
                            Forms\Components\Select::make('location_id')
                            ->label('Vender desde Bodega / Sucursal')
                            ->options(Location::query()->pluck('name', 'id'))
                            ->required()
                            ->live() // Para que el stock se actualice dinámicamente
                            ->helperText('El stock de los productos se descontará de esta ubicación.'),
                        
                            Forms\Components\Toggle::make('is_cash')
                                ->label('Venta de Contado')
                                ->default(false),
                                //->live(),    
                        ]),

                    Forms\Components\Wizard\Step::make('Items de la Venta')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->schema([
                                    Forms\Components\Select::make('product_id')
                                    ->label('Producto')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    // Este método le dice a Filament cómo buscar productos cuando el usuario escribe
                                    ->getSearchResultsUsing(function (string $search) {
                                        return \App\Models\Product::where('name', 'like', "%{$search}%")
                                            ->limit(50)
                                            ->pluck('name', 'id');
                                    })
                                    // Este método le dice a Filament cómo obtener el nombre de un producto que ya está seleccionado
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        return \App\Models\Product::find($value)?->name;
                                    })
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $product = \App\Models\Product::find($get('product_id'));
                                        if ($product) {
                                            $set('price', $product->price ?? 0);
                                            $locationId = $get('../../location_id'); 
                                            if ($locationId) {
                                                $inventory = \App\Models\Inventory::where('product_id', $product->id)
                                                    ->where('location_id', $locationId)->first();
                                                $stock = $inventory ? $inventory->stock : 0;
                                                $set('stock_info', "{$stock}");
                                            } else {
                                                $set('stock_info', "Selecciona una bodega para ver el stock.");
                                            }
                                        }
                                    }),
                                    Forms\Components\TextInput::make('stock_info')->label('Stock Actual')->readOnly(),
                                    Forms\Components\TextInput::make('quantity')->label('Cantidad')->required()->numeric()->live(onBlur: true),
                                    Forms\Components\Select::make('unit_of_measure_id')->label('Unidad de Venta')->options(\App\Models\UnitOfMeasure::query()->pluck('name', 'id'))->searchable()->preload()->required(),
                                    Forms\Components\TextInput::make('price')->label('Precio (antes de IVA)')->required()->numeric()->live(onBlur: true),
                                    Forms\Components\TextInput::make('tax_rate')->label('IVA (%)')->numeric()->required()->default(19)->live(onBlur: true),
                                ])
                                ->columns(6)
                                ->live()
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set)),
                        ]),

                    Forms\Components\Wizard\Step::make('Resumen de Totales')
                        ->schema([
                            Forms\Components\TextInput::make('subtotal')->label('Subtotal')->readOnly()->numeric()->prefix('$'),
                            Forms\Components\TextInput::make('tax')->label('Total IVA')->readOnly()->numeric()->prefix('$'),
                            Forms\Components\TextInput::make('total')->label('Total General')->readOnly()->numeric()->prefix('$'),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('id')
                ->label('N° Pedido')
                ->formatStateUsing(fn ($state) => '#' . str_pad($state, 6, '0', STR_PAD_LEFT))
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('client.name')
                ->label('Cliente')
                ->searchable()
                ->sortable(),
            Tables\Columns\BadgeColumn::make('source')
                ->label('Origen')
                ->colors([
                    'info' => 'b2b',
                    'secondary' => 'pos',
                ])
                ->formatStateUsing(fn ($state) => strtoupper($state)),
            Tables\Columns\TextColumn::make('location.name')
                ->label('Bodega/Sucursal')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('subtotal')
                ->money('cop')
                ->sortable(),
            Tables\Columns\TextColumn::make('tax')
                ->label('IVA')
                ->money('cop')
                ->sortable(),   
            Tables\Columns\TextColumn::make('total')
                ->money('cop')
                ->sortable()
                ->weight('bold'), 
            Tables\Columns\TextColumn::make('date')
                ->dateTime('d/m/Y H:i')
                ->label('Fecha')
                ->sortable(),
            Tables\Columns\BadgeColumn::make('status')
                ->label('Estado')
                ->colors(fn (Sale $record): array => 
                    $record->source === 'b2b' ? [
                        'warning' => 'Pendiente',
                        'primary' => 'Separación',
                        'success' => 'Facturado',
                        'secondary' => 'Finalizado',
                        'danger' => 'Cancelado',
                    ] : [
                        'success' => 'Pagada',
                        'warning' => 'Pendiente',
                        'danger' => 'Vencida',
                    ]
                ),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                // Filtro por origen (B2B/POS)
                SelectFilter::make('source')
                    ->label('Origen')
                    ->options([
                        'b2b' => 'B2B',
                        'pos' => 'POS',
                    ]),
                    
                // Filtro por estado
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        // Estados B2B
                        'Pendiente' => 'Pendiente',
                        'Separación' => 'Separación',
                        'Facturado' => 'Facturado',
                        'Finalizado' => 'Finalizado',
                        'Cancelado' => 'Cancelado',
                        // Estados POS
                        'Pagada' => 'Pagada',
                        'Vencida' => 'Vencida',
                    ]),
                    
                SelectFilter::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('date')
                    ->form([
                        DatePicker::make('start_date')->label('Fecha Inicio'),
                        DatePicker::make('end_date')->label('Fecha Fin'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['end_date'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->url(fn (Sale $record) => 
                            $record->source === 'b2b' 
                                ? route('filament.admin.resources.sales.edit-b2b', ['record' => $record->id])
                                : route('filament.admin.resources.sales.edit', ['record' => $record->id])
                        )
                        ->visible(fn (Sale $record) => 
                            $record->source === 'pos' || 
                            in_array($record->status, ['Pendiente', 'Separación'])
                        ),
                    Tables\Actions\Action::make('assignLots')
                        ->label('Asignar Lotes')
                        ->icon('heroicon-o-cube')
                        ->color('warning')
                        ->modalHeading('Asignar Lotes a Items del Pedido')
                        ->modalDescription('Puedes asignar uno o múltiples lotes por item')
                        ->modalWidth('4xl')
                        ->form(function (Sale $record) {
                            // Obtener items sin lotes asignados (ni en product_lot_id ni en sale_item_lots)
                            $itemsWithoutLot = $record->items()
                                ->whereNull('product_lot_id')
                                ->whereDoesntHave('lots')
                                ->get();
                            
                            if ($itemsWithoutLot->isEmpty()) {
                                return [
                                    Forms\Components\Placeholder::make('no_items')
                                        ->content('✅ Todos los items ya tienen lote asignado')
                                ];
                            }
                            
                            $sections = [];
                            foreach ($itemsWithoutLot as $item) {
                                $sections[] = Forms\Components\Section::make("Item: {$item->product->name}")
                                    ->description("Cantidad total requerida: {$item->quantity}")
                                    ->schema([
                                        Forms\Components\Repeater::make("lots_{$item->id}")
                                            ->label('Lotes')
                                            ->schema([
                                                Forms\Components\Select::make('lot_id')
                                                    ->label('Lote')
                                                    ->options(
                                                        \App\Models\ProductLot::where('product_id', $item->product_id)
                                                            ->where('is_active', true)
                                                            ->where('quantity', '>', 0)
                                                            ->orderBy('expiration_date')
                                                            ->get()
                                                            ->mapWithKeys(function ($lot) {
                                                                return [
                                                                    $lot->id => sprintf(
                                                                        '%s (Disp: %d, Vence: %s)',
                                                                        $lot->lot_number,
                                                                        $lot->quantity,
                                                                        $lot->expiration_date->format('d/m/Y')
                                                                    )
                                                                ];
                                                            })
                                                    )
                                                    ->required()
                                                    ->searchable()
                                                    ->disableOptionWhen(function ($value, $state, Forms\Get $get) {
                                                        // Obtener todos los lotes seleccionados en este repeater
                                                        $allLots = $get('../../lots_' . $get('../../../id')) ?? [];
                                                        
                                                        // Extraer los lot_id ya seleccionados (excepto el actual)
                                                        $selectedLots = collect($allLots)
                                                            ->pluck('lot_id')
                                                            ->filter(fn($id) => $id !== null && $id !== $state)
                                                            ->all();
                                                        
                                                        // Deshabilitar si el lote ya está seleccionado
                                                        return in_array($value, $selectedLots);
                                                    }),
                                                Forms\Components\TextInput::make('quantity')
                                                    ->label('Cantidad')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(0.01)
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                        // Validar que no exceda la cantidad del lote
                                                        $lotId = $get('lot_id');
                                                        if ($lotId) {
                                                            $lot = \App\Models\ProductLot::find($lotId);
                                                            if ($lot && $state > $lot->quantity) {
                                                                $set('quantity', $lot->quantity);
                                                            }
                                                        }
                                                    }),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('+ Agregar otro lote')
                                            ->reorderable(false)
                                            ->collapsible()
                                            ->defaultItems(1),
                                        Forms\Components\Placeholder::make("total_assigned_{$item->id}")
                                            ->label('Total Asignado')
                                            ->content(function (Forms\Get $get) use ($item) {
                                                $lots = $get("lots_{$item->id}") ?? [];
                                                // Convertir a float y filtrar valores vacíos
                                                $total = collect($lots)
                                                    ->pluck('quantity')
                                                    ->filter(fn($q) => $q !== null && $q !== '')
                                                    ->map(fn($q) => (float)$q)
                                                    ->sum();
                                                $required = (float)$item->quantity;
                                                $remaining = $required - $total;
                                                
                                                $color = $total == $required ? 'green' : ($total > $required ? 'red' : 'orange');
                                                $icon = $total == $required ? '✅' : ($total > $required ? '❌' : '⚠️');
                                                
                                                return new \Illuminate\Support\HtmlString(
                                                    "<div style='font-size: 1.1em; font-weight: bold; color: {$color};'>" .
                                                    "{$icon} {$total} / {$required} unidades" .
                                                    ($remaining != 0 ? " (Faltan: {$remaining})" : " (Completo)") .
                                                    "</div>"
                                                );
                                            }),
                                    ])
                                    ->collapsible();
                            }
                            
                            return $sections;
                        })
                        ->action(function (Sale $record, array $data) {
                            $itemsWithoutLot = $record->items()
                                ->whereNull('product_lot_id')
                                ->whereDoesntHave('lots')
                                ->get();
                            
                            $errors = [];
                            
                            // Validar todas las asignaciones
                            foreach ($itemsWithoutLot as $item) {
                                $lotsKey = "lots_{$item->id}";
                                $lots = $data[$lotsKey] ?? [];
                                
                                if (empty($lots)) {
                                    $errors[] = "{$item->product->name}: No se asignaron lotes";
                                    continue;
                                }
                                
                                // Validar que no haya lotes duplicados
                                $lotIds = collect($lots)->pluck('lot_id')->filter();
                                if ($lotIds->count() !== $lotIds->unique()->count()) {
                                    $errors[] = "{$item->product->name}: No puedes seleccionar el mismo lote más de una vez";
                                }
                                
                                // Validar que la suma de cantidades = cantidad del item
                                $totalAssigned = collect($lots)
                                    ->pluck('quantity')
                                    ->filter(fn($q) => $q !== null && $q !== '')
                                    ->map(fn($q) => (float)$q)
                                    ->sum();
                                if ($totalAssigned != $item->quantity) {
                                    $errors[] = "{$item->product->name}: Total asignado ({$totalAssigned}) no coincide con cantidad requerida ({$item->quantity})";
                                }
                                
                                // Validar que cada lote tenga suficiente stock
                                foreach ($lots as $lotData) {
                                    $lot = \App\Models\ProductLot::find($lotData['lot_id']);
                                    if ($lot && $lotData['quantity'] > $lot->quantity) {
                                        $errors[] = "{$item->product->name}: Cantidad ({$lotData['quantity']}) excede stock del lote {$lot->lot_number} ({$lot->quantity})";
                                    }
                                }
                            }
                            
                            // Si hay errores, mostrarlos y detener
                            if (!empty($errors)) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('Errores de Validación')
                                    ->body(implode("\n", $errors))
                                    ->persistent()
                                    ->send();
                                return;
                            }
                            
                            // Si todo está bien, crear los registros en sale_item_lots
                            foreach ($itemsWithoutLot as $item) {
                                $lotsKey = "lots_{$item->id}";
                                $lots = $data[$lotsKey] ?? [];
                                
                                foreach ($lots as $lotData) {
                                    $lot = \App\Models\ProductLot::find($lotData['lot_id']);
                                    
                                    \App\Models\SaleItemLot::create([
                                        'sale_item_id' => $item->id,
                                        'product_lot_id' => $lotData['lot_id'],
                                        'quantity' => $lotData['quantity'],
                                        'lot_number' => $lot->lot_number,
                                        'expiration_date' => $lot->expiration_date,
                                    ]);
                                }
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Lotes Asignados')
                                ->body('Los lotes han sido asignados exitosamente')
                                ->send();
                        })
                        ->visible(fn (Sale $record) => 
                            $record->source === 'b2b' && 
                            in_array($record->status, ['Pendiente', 'Separación']) &&
                            $record->items()->whereNull('product_lot_id')->whereDoesntHave('lots')->count() > 0
                        ),
                    Tables\Actions\Action::make('changeStatus')
                        ->label('Cambiar Estado')
                        ->icon('heroicon-o-arrow-path')
                        ->color('primary')
                        ->modalWidth('2xl')
                        ->form([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('status')
                                        ->label('Nuevo Estado')
                                        ->options(fn (Sale $record) => match($record->status) {
                                            'Pendiente' => [
                                                'Separación' => 'Separación',
                                                'Facturado' => 'Facturado',
                                            ],
                                            'Separación' => [
                                                'Facturado' => 'Facturado',
                                            ],
                                            'Facturado' => [
                                                'Finalizado' => 'Finalizado',
                                            ],
                                            default => [],
                                        })
                                        ->required()
                                        ->live()
                                        ->helperText(fn (Sale $record) => 
                                            $record->status === 'Separación' 
                                                ? " Al cambiar a 'Facturado' se creará la cuenta por cobrar y se descontará el inventario"
                                                : null
                                        ),
                                    
                                    Forms\Components\DateTimePicker::make('invoiced_at')
                                        ->label('Fecha de Facturación')
                                        ->default(now())
                                        ->required()
                                        ->native(false)
                                        ->helperText('Fecha y hora en que se generó la factura')
                                        ->visible(fn (Forms\Get $get) => $get('status') === 'Facturado'),
                                ]),
                            
                            Forms\Components\TextInput::make('invoice_number')
                                ->label('Número de Factura')
                                ->required()
                                ->maxLength(50)
                                ->helperText('Ingresa el número de factura')
                                ->visible(fn (Forms\Get $get) => $get('status') === 'Facturado'),
                            
                            Forms\Components\FileUpload::make('invoice_pdf')
                                ->label('PDF de Factura')
                                ->disk('public')
                                ->directory('invoices')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(5120) // 5MB
                                ->required()
                                ->helperText('Sube el PDF de la factura (máx. 5MB)')
                                ->visible(fn (Forms\Get $get) => $get('status') === 'Facturado'),
                        ])
                        ->action(function (Sale $record, array $data) {
                            // Validar lotes si se va a cambiar a Facturado
                            if ($data['status'] === 'Facturado') {
                                // Cargar la relación lots para verificar correctamente
                                $record->load('items.lots');
                                
                                // Contar items sin lotes asignados en sale_item_lots
                                $itemsWithoutLot = 0;
                                foreach ($record->items as $item) {
                                    \Log::info("Validando item #{$item->id}", [
                                        'lots_count' => $item->lots->count(),
                                        'lots' => $item->lots->pluck('lot_number')->toArray()
                                    ]);
                                    
                                    // Verificar SOLO si tiene lotes en sale_item_lots
                                    if ($item->lots->count() === 0) {
                                        $itemsWithoutLot++;
                                        \Log::warning("Item #{$item->id} sin lote asignado en sale_item_lots");
                                    }
                                }
                                    
                                if ($itemsWithoutLot > 0) {
                                    \Filament\Notifications\Notification::make()
                                        ->danger()
                                        ->title('Error: Items sin Lote')
                                        ->body("No se puede facturar: {$itemsWithoutLot} items sin lote asignado. Usa la acción 'Asignar Lotes' primero.")
                                        ->persistent()
                                        ->send();
                                    return;
                                }
                                
                                // Validar número de factura ANTES de cambiar estado
                                if ($data['status'] === 'Facturado' && !empty($data['invoice_number'])) {
                                    // Verificar si el número de factura ya existe
                                    $existingInvoice = \App\Models\AccountReceivable::where('invoice_number', $data['invoice_number'])->first();
                                    
                                    if ($existingInvoice) {
                                        \Filament\Notifications\Notification::make()
                                            ->danger()
                                            ->title('Error: Número de Factura Duplicado')
                                            ->body("El número de factura '{$data['invoice_number']}' ya existe en el sistema.\n\nCada factura debe tener un número único. Por favor:\n1. Verifica el número de factura en tu sistema externo\n2. Ingresa un número de factura diferente")
                                            ->persistent()
                                            ->send();
                                        return;
                                    }
                                }
                                
                                // Guardar el número de factura en la sesión para que el Observer lo use
                                if (!empty($data['invoice_number'])) {
                                    session(['pending_invoice_number_' . $record->id => $data['invoice_number']]);
                                }
                            }
                            
                            
                            try {
                                $record->status = $data['status'];
                                
                                // Si se está facturando, guardar también el PDF y la fecha
                                if ($data['status'] === 'Facturado') {
                                    if (!empty($data['invoice_pdf'])) {
                                        $record->invoice_pdf_path = $data['invoice_pdf'];
                                    }
                                    if (!empty($data['invoiced_at'])) {
                                        $record->invoiced_at = $data['invoiced_at'];
                                    }
                                }
                                
                                $record->save();
                                
                                \Filament\Notifications\Notification::make()
                                    ->success()
                                    ->title('Estado Actualizado')
                                    ->body("El pedido ahora está en estado: {$data['status']}")
                                    ->send();
                            } catch (\Exception $e) {
                                // Revertir el estado si hubo error
                                $record->refresh();
                                
                                $message = $e->getMessage();
                                
                                if (str_contains($message, 'ya fue facturado')) {
                                    $message = "Este pedido ya fue facturado anteriormente.\n\nVerifica en 'Cuentas por Cobrar' si ya existe una cuenta para este pedido.";
                                }
                                
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('Error al Cambiar Estado')
                                    ->body($message)
                                    ->persistent()
                                    ->send();
                            }
                        })
                        ->visible(fn (Sale $record) => 
                            $record->source === 'b2b' && 
                            !in_array($record->status, ['Finalizado', 'Cancelado'])
                        ),
                    Tables\Actions\Action::make('cancelInvoice')
                        ->label('Cancelar Factura')
                        ->icon('heroicon-o-receipt-refund')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Cancelar Factura')
                        ->modalDescription('Esta acción devolverá el inventario y cancelará la cuenta por cobrar.')
                        ->form([
                            Forms\Components\Textarea::make('cancellation_reason')
                                ->label('Motivo de Cancelación')
                                ->required()
                                ->rows(3)
                                ->placeholder('Ej: Cliente rechazó el pedido, Error en facturación, etc.')
                        ])
                        ->action(function (Sale $record, array $data) {
                            try {
                                // Cargar relaciones necesarias
                                $record->load(['items.lots', 'accountReceivable']);
                                
                                // 1. Devolver inventario
                                foreach ($record->items as $item) {
                                    if ($item->lots->count() > 0) {
                                        foreach ($item->lots as $saleItemLot) {
                                            $lot = \App\Models\ProductLot::find($saleItemLot->product_lot_id);
                                            
                                            if ($lot) {
                                                // Devolver cantidad
                                                $lot->increment('quantity', $saleItemLot->quantity);
                                                
                                                // Reactivar lote si estaba inactivo
                                                if (!$lot->is_active) {
                                                    $lot->update(['is_active' => true]);
                                                }
                                                
                                                // Registrar movimiento de entrada
                                                \App\Models\StockMovement::create([
                                                    'product_id' => $item->product_id,
                                                    'product_lot_id' => $lot->id,
                                                    'location_id' => $record->location_id,
                                                    'type' => 'entrada',
                                                    'quantity' => $saleItemLot->quantity,
                                                    'reference_type' => 'App\Models\Sale',
                                                    'reference_id' => $record->id,
                                                    'notes' => "Devolución por cancelación de factura #{$record->id}",
                                                ]);
                                            }
                                        }
                                    }
                                }
                                
                                // 2. Marcar cuenta por cobrar como cancelada
                                if ($record->accountReceivable) {
                                    $record->accountReceivable->update([
                                        'status' => 'cancelled',
                                        'cancellation_reason' => $data['cancellation_reason'],
                                        'cancelled_at' => now(),
                                    ]);
                                }
                                
                                // 3. Cambiar estado del pedido a Cancelado
                                $record->update(['status' => 'Cancelado']);
                                
                                \Filament\Notifications\Notification::make()
                                    ->success()
                                    ->title('Factura Cancelada')
                                    ->body('El inventario ha sido devuelto y la cuenta por cobrar ha sido cancelada.')
                                    ->send();
                                    
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('Error al Cancelar Factura')
                                    ->body($e->getMessage())
                                    ->persistent()
                                    ->send();
                            }
                        })
                        ->visible(fn (Sale $record) => 
                            $record->source === 'b2b' && 
                            $record->status === 'Facturado'
                        ),
                    Tables\Actions\Action::make('cancel')
                        ->label('Cancelar Pedido')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Cancelar Pedido')
                        ->modalDescription('¿Estás seguro de cancelar este pedido? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, cancelar')
                        ->action(function (Sale $record) {
                            if (in_array($record->status, ['Facturado', 'Finalizado'])) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('Error')
                                    ->body('No se puede cancelar un pedido facturado o finalizado')
                                    ->send();
                                return;
                            }
                            
                            $record->status = 'Cancelado';
                            $record->save();
                            
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Pedido Cancelado')
                                ->body('El pedido ha sido cancelado exitosamente')
                                ->send();
                        })
                        ->visible(fn (Sale $record) => 
                            $record->source === 'b2b' && 
                            in_array($record->status, ['Pendiente', 'Separación'])
                        ),
                    
                    // Ver factura PDF subida
                    Tables\Actions\Action::make('viewInvoice')
                        ->label('Ver Factura')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->url(fn (Sale $record) => $record->getInvoicePdfUrl())
                        ->openUrlInNewTab()
                        ->visible(fn (Sale $record) => $record->hasInvoicePdf()),
                    
                    // Descargar factura PDF subida
                    Tables\Actions\Action::make('downloadInvoice')
                        ->label('Descargar Factura')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Sale $record) {
                            if ($record->hasInvoicePdf()) {
                                return \Storage::disk('public')->download(
                                    $record->invoice_pdf_path,
                                    "factura-{$record->invoice_number}.pdf"
                                );
                            }
                        })
                        ->visible(fn (Sale $record) => $record->hasInvoicePdf()),
                    
                    Tables\Actions\Action::make('downloadPdf')
                        ->label('Descargar Pedido PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Sale $record) {
                            $sale = $record->load(['client', 'business', 'items.product.unitOfMeasure', 'items.unitOfMeasure']);
                            $pdf = Pdf::loadView('pdf.invoice', ['sale' => $sale]);
                            return response()->streamDownload(fn() => print($pdf->output()), "pedido-{$sale->id}.pdf");
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'create-b2b' => Pages\CreateB2BOrderPage::route('/create-b2b'),
            'edit-b2b' => Pages\EditB2BOrderPage::route('/{record}/edit-b2b'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
            'view' => Pages\ViewSale::route('/{record}'),
        ];
    }

    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('business_id', auth()->user()->business_id)
            ->with([
                'client',
                'location',
                'items.product',
                'items.unitOfMeasure',
            ]);
    }
    
    public static function updateTotals(Get $get, Set $set): void
    {
        $items = $get('items') ?? [];
        $subtotal = 0;
        $taxTotal = 0;

        // CAMBIO: Se vuelve a calcular el subtotal y el IVA por separado
        foreach ($items as $item) {
            $itemSubtotal = (float)($item['quantity'] ?? 0) * (float)($item['price'] ?? 0);
            $subtotal += $itemSubtotal;
            $taxTotal += $itemSubtotal * ((float)($item['tax_rate'] ?? 0) / 100);
        }
        
        $set('subtotal', $subtotal);
        $set('tax', $taxTotal);
        $set('total', $subtotal + $taxTotal);
    }
}


