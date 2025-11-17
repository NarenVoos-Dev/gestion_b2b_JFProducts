<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model; 


class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    // Lo agrupamos junto a Proveedores
    protected static ?string $navigationGroup = 'Catalogos';
    protected static ?int $navigationSort = 32;
    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Cliente B2B')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Información Básica')
                            ->schema([
                                Forms\Components\Hidden::make('business_id')->default(auth()->user()->business_id),
                                
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre de Empresa/Cliente')
                                    ->required()
                                    ->columnSpanFull()
                                    ->maxLength(255)
                                    ->validationMessages([
                                        'required' => 'El nombre de la empresa es obligatorio.',
                                    ]),

                                Forms\Components\Grid::make(3) // Define 3 columnas para este grupo
                                    ->schema([
                                        Forms\Components\Select::make('type_document')
                                            ->label('Tipo de Documento')
                                            ->options([
                                                'NIT' => 'NIT',
                                                'CC' => 'Cédula de Ciudadanía (CC)',
                                                'CE' => 'Cédula de Extranjería (CE)',
                                            ])
                                            ->default('NIT')
                                            ->required()
                                            ->validationMessages([
                                                'required' => 'El tipo de documento es obligatorio.',
                                            ]), 

                                        Forms\Components\TextInput::make('document')
                                            ->label('Documento (NIT/Cédula)')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->validationMessages([
                                                'required' => 'El documento es obligatorio.',
                                                'unique' => 'Este número de documento ya está registrado.',
                                            ]),
                                            
                                        Forms\Components\TextInput::make('email')
                                            ->label('Correo Electrónico')
                                            ->email()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->required(fn (string $operation, ?Client $record): bool => 
                                                $operation === 'edit' && 
                                                $record !== null && 
                                                $record->is_active
                                            )
                                            ->validationMessages([
                                                'required' => 'Este campo es obligatorio.',
                                            ])
                                            ->helperText('Obligatorio para crear acceso al portal B2B'),
                                    ]),
                                    
                                Forms\Components\TextInput::make('address')
                                    ->label('Dirección de Envío Principal')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Contacto y Crédito')
                            ->schema([
                                Forms\Components\TextInput::make('phone1')
                                    ->label('Teléfono Principal')
                                    ->tel()
                                    ->required()
                                    ->maxLength(255)
                                    ->validationMessages([
                                        'required' => 'Debe registrar al menos un numero de telefono.',
                                    ]), 
                                
                                Forms\Components\TextInput::make('phone2')
                                    ->label('Teléfono Secundario')
                                    ->tel()
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('credit_limit')
                                    ->label('Límite de Crédito')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0)
                                    ->required(),
                                
                                Forms\Components\Select::make('price_list_id')
                                    ->label('Lista de Precios Asignada')
                                    ->relationship('priceList', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Usar precio estándar (sin lista)'),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Acceso B2B')
                            ->schema([
                                Forms\Components\Placeholder::make('estado_acceso')
                                    ->content(function (?Client $record, string $operation): string {
                                        if ($operation === 'create' || $record === null) {
                                            return 'El estado de acceso se configurará al guardar el nuevo cliente.';
                                        }
                                        
                                        if (!$record->email) {
                                            return '⚠️ Este cliente NO tiene correo electrónico. Debe registrar un email antes de activar el acceso.';
                                        }
                                        
                                        return $record->is_active 
                                            ? '✅ Este cliente tiene acceso activo al portal.' 
                                            : '⏳ El cliente está pendiente de aprobación.';
                                    })
                                    ->columnSpanFull(),
                                
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Autorizar Acceso al Portal (Estado Activo)')
                                    ->helperText('Activa este interruptor para dar acceso al cliente al Portal de Pedidos B2B.')
                                    ->default(false)
                                    ->hiddenOn('create')
                                    ->disabled(fn (?Client $record): bool => 
                                        $record !== null && empty($record->email)
                                    ),
                                
                                Forms\Components\TextInput::make('user_password')
                                    ->label('Contraseña Temporal (Solo para Clientes Nuevos)')
                                    ->password()
                                    ->dehydrated(false) // CORRECCIÓN: Cambiar a false para que no se guarde
                                    ->required(fn (string $operation, ?Client $record): bool => 
                                        $operation === 'edit' && 
                                        $record !== null && 
                                        $record->is_active && 
                                        $record->user === null
                                    )
                                    ->maxLength(255)
                                    ->hidden(fn (string $operation, ?Client $record): bool => 
                                        $operation === 'create' || 
                                        $record === null || 
                                        $record->user !== null
                                    )
                                    ->validationMessages([
                                        'required' => 'Debe registrar al menos un numero de telefono.',
                                    ])
                                    ->autocomplete('new-password'),
                            ])
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_active')
                    ->label('ACCESO B2B')
                    ->boolean()
                    ->sortable()
                    ->tooltip('Controla el acceso al Portal B2B'),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre del Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('document')
                    ->label('Documento/NIT')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('credit_limit')
                    ->label('Límite Crédito')
                    ->money('COP')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.estado')
                    ->label('Estado Usuario')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'activo' => 'success',
                        'inactivo' => 'danger',
                        'pendiente' => 'warning',
                        default => 'gray',
                    })
                    ->placeholder('N/A'),
            ])
            ->filters([
                Filter::make('is_active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Clientes Activos (Portal)')
                    ->toggle(),
                    
                Filter::make('pending_approval')
                    ->label('Pendientes de Aprobación')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', false))
                    ->indicator('Pendiente B2B')
                    ->default(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }    

    /**
     * Filtro multiempresa
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }
}