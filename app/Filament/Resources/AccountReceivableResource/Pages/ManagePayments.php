<?php

namespace App\Filament\Resources\AccountReceivableResource\Pages;

use App\Filament\Resources\AccountReceivableResource;
use App\Models\AccountPayment;
use App\Models\AccountReceivable;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ManagePayments extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = AccountReceivableResource::class;

    protected static string $view = 'filament.resources.account-receivable-resource.pages.manage-payments';
    
    public function getTitle(): string
    {
        return 'Pagos - Factura ' . $this->record->invoice_number;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AccountPayment::query()->where('account_receivable_id', $this->record->id))
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 2))
                    ->color(fn ($record) => $record->amount > 0 ? 'success' : 'warning')
                    ->weight('bold')
                    ->description(fn ($record) => $record->amount == 0 ? 'Pendiente de aprobación' : null),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Método')
                    ->default('-'),
                Tables\Columns\TextColumn::make('reference')
                    ->label('Referencia')
                    ->default('-')
                    ->limit(20),
                Tables\Columns\TextColumn::make('payment_proof_path')
                    ->label('Comprobante')
                    ->formatStateUsing(fn ($record) => $record->hasProof() ? 'Ver' : '-')
                    ->url(fn ($record) => $record->hasProof() ? $record->getProofUrl() : null)
                    ->openUrlInNewTab()
                    ->color('info'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->notes)
                    ->default('-'),
                Tables\Columns\IconColumn::make('approved')
                    ->label('Estado')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->amount > 0)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->amount == 0)
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Monto del Pago')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->maxValue($this->record->balance)
                            ->helperText('Saldo pendiente: $' . number_format($this->record->balance, 0)),
                        Forms\Components\Select::make('payment_method')
                            ->label('Método de Pago')
                            ->options([
                                'Efectivo' => 'Efectivo',
                                'Transferencia' => 'Transferencia',
                                'Cheque' => 'Cheque',
                                'Tarjeta' => 'Tarjeta',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('reference')
                            ->label('Referencia')
                            ->maxLength(100),
                    ])
                    ->action(function (AccountPayment $record, array $data) {
                        // Actualizar el pago
                        $record->update([
                            'amount' => $data['amount'],
                            'payment_method' => $data['payment_method'],
                            'reference' => $data['reference'] ?? null,
                            'notes' => 'Pago aprobado por administrador',
                        ]);
                        
                        // Actualizar balance de la cuenta
                        $this->record->balance -= $data['amount'];
                        
                        // Actualizar estado (solo marcar como pagado si balance es 0 o negativo)
                        if ($this->record->balance <= 0) {
                            $this->record->status = 'paid';
                            $this->record->balance = 0; // Asegurar que quede en 0
                        } elseif ($this->record->balance < $this->record->amount) {
                            $this->record->status = 'partial';
                        }
                        
                        $this->record->save();
                        
                        Notification::make()
                            ->success()
                            ->title('Pago Aprobado')
                            ->body('Pago de $' . number_format($data['amount'], 0) . ' aprobado exitosamente')
                            ->send();
                    }),
            ])
            ->defaultSort('payment_date', 'desc');
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('registerPayment')
                ->label('Registrar Pago')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->visible(fn () => $this->record->status !== 'paid')
                ->form([
                    Forms\Components\DatePicker::make('payment_date')
                        ->label('Fecha de Pago')
                        ->required()
                        ->default(now())
                        ->maxDate(now()),
                    Forms\Components\TextInput::make('amount')
                        ->label('Monto del Pago')
                        ->numeric()
                        ->required()
                        ->prefix('$')
                        ->maxValue($this->record->balance)
                        ->helperText('Saldo pendiente: $' . number_format($this->record->balance, 0)),
                    Forms\Components\Select::make('payment_method')
                        ->label('Método de Pago')
                        ->options([
                            'Efectivo' => 'Efectivo',
                            'Transferencia' => 'Transferencia',
                            'Cheque' => 'Cheque',
                            'Tarjeta' => 'Tarjeta',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('reference')
                        ->label('Referencia')
                        ->maxLength(100),
                    Forms\Components\FileUpload::make('payment_proof')
                        ->label('Comprobante de Pago')
                        ->disk('local')
                        ->directory('payment-proofs')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(5120),
                    Forms\Components\Textarea::make('notes')
                        ->label('Notas')
                        ->rows(2)
                        ->maxLength(500),
                ])
                ->action(function (array $data) {
                    // Crear el pago
                    $payment = AccountPayment::create([
                        'account_receivable_id' => $this->record->id,
                        'payment_date' => $data['payment_date'],
                        'amount' => $data['amount'],
                        'payment_method' => $data['payment_method'],
                        'reference' => $data['reference'] ?? null,
                        'payment_proof_path' => $data['payment_proof'] ?? null,
                        'notes' => $data['notes'] ?? 'Pago registrado por administrador',
                        'created_by' => auth()->id(),
                    ]);
                    
                    // Actualizar balance
                    $this->record->balance -= $data['amount'];
                    
                    // Actualizar estado (solo marcar como pagado si balance es 0 o negativo)
                    if ($this->record->balance <= 0) {
                        $this->record->status = 'paid';
                        $this->record->balance = 0; // Asegurar que quede en 0
                    } elseif ($this->record->balance < $this->record->amount) {
                        $this->record->status = 'partial';
                    }
                    
                    $this->record->save();
                    
                    Notification::make()
                        ->success()
                        ->title('Pago Registrado')
                        ->body('Pago de $' . number_format($data['amount'], 0) . ' registrado exitosamente')
                        ->send();
                }),
            Actions\Action::make('back')
                ->label('Volver')
                ->url(AccountReceivableResource::getUrl('index'))
                ->color('gray'),
        ];
    }
}
