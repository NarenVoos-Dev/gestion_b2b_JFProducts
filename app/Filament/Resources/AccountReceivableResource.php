<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountReceivableResource\Pages;
use App\Models\AccountReceivable;
use App\Models\AccountPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class AccountReceivableResource extends Resource
{
    protected static ?string $model = AccountReceivable::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    protected static ?string $navigationLabel = 'Cuentas por Cobrar';
    
    protected static ?string $navigationGroup = 'Finanzas';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Cuenta')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Nº Factura')
                            ->disabled(),
                        Forms\Components\Select::make('sale_id')
                            ->label('Pedido')
                            ->relationship('sale', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => '#' . str_pad($record->id, 6, '0', STR_PAD_LEFT))
                            ->disabled(),
                        Forms\Components\Select::make('client_id')
                            ->label('Cliente')
                            ->relationship('client', 'name')
                            ->disabled(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Monto Total')
                            ->numeric()
                            ->prefix('$')
                            ->disabled(),
                        Forms\Components\TextInput::make('balance')
                            ->label('Saldo Pendiente')
                            ->numeric()
                            ->prefix('$')
                            ->disabled(),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Fecha de Vencimiento'),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'partial' => 'Pago Parcial',
                                'paid' => 'Pagado',
                                'cancelled' => 'Cancelada',
                            ])
                            ->disabled(),
                        Forms\Components\Textarea::make('cancellation_reason')
                            ->label('Motivo de Cancelación')
                            ->rows(3)
                            ->visible(fn ($record) => $record?->status === 'cancelled')
                            ->disabled(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Nº Factura')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_id')
                    ->label('Pedido')
                    ->formatStateUsing(fn ($state) => '#' . str_pad($state, 6, '0', STR_PAD_LEFT))
                    ->url(fn ($record) => route('filament.admin.resources.sales.view', ['record' => $record->sale_id]))
                    ->color('primary'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('COP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Saldo')
                    ->money('COP')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : null),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'partial',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'Pendiente',
                        'partial' => 'Pago Parcial',
                        'paid' => 'Pagado',
                        'cancelled' => 'Cancelada',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'partial' => 'Pago Parcial',
                        'paid' => 'Pagado',
                        'cancelled' => 'Cancelada',
                    ]),
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('overdue')
                    ->label('Vencidas')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<', now())->where('status', '!=', 'paid')),
            ])
            ->actions([
                Tables\Actions\Action::make('registerPayment')
                    ->label('Registrar Pago')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Monto del Pago')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->maxValue(fn ($record) => $record->balance),
                        Forms\Components\Select::make('payment_method')
                            ->label('Método de Pago')
                            ->options([
                                'Efectivo' => 'Efectivo',
                                'Transferencia' => 'Transferencia',
                                'Cheque' => 'Cheque',
                                'Tarjeta' => 'Tarjeta',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Fecha de Pago')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('reference')
                            ->label('Referencia')
                            ->maxLength(100),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(2),
                    ])
                    ->action(function (AccountReceivable $record, array $data) {
                        // Crear el pago
                        AccountPayment::create([
                            'account_receivable_id' => $record->id,
                            'amount' => $data['amount'],
                            'payment_method' => $data['payment_method'],
                            'payment_date' => $data['payment_date'],
                            'reference' => $data['reference'] ?? null,
                            'notes' => $data['notes'] ?? null,
                            'created_by' => auth()->id(),
                        ]);
                        
                        // Actualizar balance
                        $record->balance -= $data['amount'];
                        
                        // Actualizar estado
                        if ($record->balance <= 0) {
                            $record->status = 'paid';
                            $record->balance = 0;
                        } elseif ($record->balance < $record->amount) {
                            $record->status = 'partial';
                        }
                        
                        $record->save();
                        
                        Notification::make()
                            ->success()
                            ->title('Pago Registrado')
                            ->body("Pago de $" . number_format($data['amount'], 0) . " registrado exitosamente")
                            ->send();
                    })
                    ->visible(fn ($record) => !in_array($record->status, ['paid', 'cancelled'])),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No permitir eliminación masiva
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
            'index' => Pages\ListAccountReceivables::route('/'),
        ];
    }
}
