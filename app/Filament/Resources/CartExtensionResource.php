<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CartExtensionResource\Pages;
use App\Models\CartItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class CartExtensionResource extends Resource
{
    protected static ?string $model = CartItem::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    
    protected static ?string $navigationLabel = 'Solicitudes de Prórroga';
    
    protected static ?string $modelLabel = 'Solicitud de Prórroga';
    
    protected static ?string $pluralModelLabel = 'Solicitudes de Prórroga';
    
    protected static ?string $navigationGroup = 'B2B';
    
    protected static ?int $navigationSort = 5;
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('extension_status', 'pending')->count() ?: null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('extension_status', 'pending')
            ->with(['user.client', 'product']);
    }
    
    public static function canCreate(): bool
    {
        return false;
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->limit(40)
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('extension_count')
                    ->label('Prórrogas Usadas')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        0 => 'success',
                        1 => 'warning',
                        2 => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn ($state) => "{$state}/3"),
                
                Tables\Columns\TextColumn::make('expiration_date')
                    ->label('Expira')
                    ->dateTime('d/m/Y H:i')
                    ->color('danger')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('extension_requested_at')
                    ->label('Solicitado')
                    ->dateTime('d/m/Y H:i')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('extension_requested_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Prórroga')
                    ->modalDescription(fn (CartItem $record) => 
                        "¿Aprobar prórroga para {$record->user->client->name}? El carrito se extenderá por " . config('cart.expiration_minutes') . " minutos más."
                    )
                    ->action(function (CartItem $record) {
                        self::approveExtension($record);
                    }),
                
                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rechazar Prórroga')
                    ->modalDescription('¿Estás seguro de rechazar esta solicitud de prórroga?')
                    ->action(function (CartItem $record) {
                        self::rejectExtension($record);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('approveAll')
                    ->label('Aprobar Seleccionados')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            self::approveExtension($record);
                        }
                    }),
            ]);
    }
    
    protected static function approveExtension(CartItem $item)
    {
        // Aprobar todos los items del mismo usuario
        CartItem::where('user_id', $item->user_id)
            ->where('extension_status', 'pending')
            ->update([
                'expiration_date' => now()->addMinutes(config('cart.expiration_minutes', 2)),
                'extension_status' => 'approved',
                'extension_count' => \DB::raw('extension_count + 1'),
            ]);
        
        Notification::make()
            ->success()
            ->title('Prórroga Aprobada')
            ->body("Se extendió el carrito del cliente {$item->user->client->name} por " . config('cart.expiration_minutes') . " minutos más")
            ->send();
    }
    
    protected static function rejectExtension(CartItem $item)
    {
        CartItem::where('user_id', $item->user_id)
            ->where('extension_status', 'pending')
            ->update([
                'extension_status' => 'rejected',
            ]);
        
        Notification::make()
            ->warning()
            ->title('Prórroga Rechazada')
            ->body("Se rechazó la prórroga del cliente {$item->user->client->name}")
            ->send();
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCartExtensions::route('/'),
        ];
    }
}
