<?php

namespace App\Filament\Resources\BusinessResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;

class PaymentMethodsRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentMethods';
    
    protected static ?string $title = 'Métodos de Pago';
    
    protected static ?string $modelLabel = 'método de pago';
    
    protected static ?string $pluralModelLabel = 'métodos de pago';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipo de Método')
                            ->options([
                                'bank_account' => 'Cuenta Bancaria',
                                'qr_code' => 'Código QR',
                                'payment_link' => 'Link de Pago',
                                'cash' => 'Efectivo',
                                'other' => 'Otro',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('type', $state)),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->inline(false),
                        
                        Forms\Components\TextInput::make('display_order')
                            ->label('Orden de Visualización')
                            ->numeric()
                            ->default(0)
                            ->helperText('Menor número aparece primero'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Información Bancaria')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Nombre del Banco')
                            ->maxLength(255)
                            ->required(fn (Get $get) => $get('type') === 'bank_account'),
                        
                        Forms\Components\Select::make('account_type')
                            ->label('Tipo de Cuenta')
                            ->options([
                                'ahorros' => 'Ahorros',
                                'corriente' => 'Corriente',
                            ])
                            ->required(fn (Get $get) => $get('type') === 'bank_account'),
                        
                        Forms\Components\TextInput::make('account_number')
                            ->label('Número de Cuenta')
                            ->maxLength(255)
                            ->required(fn (Get $get) => $get('type') === 'bank_account'),
                        
                        Forms\Components\TextInput::make('account_holder')
                            ->label('Titular de la Cuenta')
                            ->maxLength(255)
                            ->required(fn (Get $get) => $get('type') === 'bank_account'),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get) => $get('type') === 'bank_account'),
                
                Forms\Components\Section::make('Código QR')
                    ->schema([
                        Forms\Components\FileUpload::make('qr_code_image')
                            ->label('Imagen del QR')
                            ->image()
                            ->directory('payment-qr-codes')
                            ->required(fn (Get $get) => $get('type') === 'qr_code')
                            ->helperText('Sube la imagen del código QR para pagos'),
                    ])
                    ->visible(fn (Get $get) => $get('type') === 'qr_code'),
                
                Forms\Components\Section::make('Link de Pago')
                    ->schema([
                        Forms\Components\TextInput::make('payment_link')
                            ->label('URL de Pago')
                            ->url()
                            ->maxLength(255)
                            ->required(fn (Get $get) => $get('type') === 'payment_link')
                            ->helperText('Ej: https://pse.com/pagar, https://nequi.com/...'),
                    ])
                    ->visible(fn (Get $get) => $get('type') === 'payment_link'),
                
                Forms\Components\Section::make('Información Adicional')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción o Instrucciones')
                            ->rows(3)
                            ->maxLength(65535)
                            ->helperText('Información adicional para los clientes'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'bank_account' => 'Cuenta Bancaria',
                        'qr_code' => 'Código QR',
                        'payment_link' => 'Link de Pago',
                        'cash' => 'Efectivo',
                        'other' => 'Otro',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'bank_account' => 'success',
                        'qr_code' => 'info',
                        'payment_link' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Banco')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('account_number')
                    ->label('Número de Cuenta')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('display_order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->defaultSort('display_order')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'bank_account' => 'Cuenta Bancaria',
                        'qr_code' => 'Código QR',
                        'payment_link' => 'Link de Pago',
                        'cash' => 'Efectivo',
                        'other' => 'Otro',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
