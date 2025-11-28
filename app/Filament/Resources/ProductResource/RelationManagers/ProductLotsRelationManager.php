<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Location;

class ProductLotsRelationManager extends RelationManager
{
    protected static string $relationship = 'productLots';
    
    protected static ?string $title = 'Lotes del Producto';
    
    protected static ?string $modelLabel = 'Lote';
    
    protected static ?string $pluralModelLabel = 'Lotes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('location_id')
                    ->label('Bodega')
                    ->options(Location::where('business_id', auth()->user()->business_id)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Forms\Components\TextInput::make('lot_number')
                    ->label('Número de Lote')
                    ->required()
                    ->maxLength(100),
                
                Forms\Components\TextInput::make('quantity')
                    ->label('Cantidad')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->suffix('und'),
                
                Forms\Components\TextInput::make('cost')
                    ->label('Costo Unitario')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('$'),
                
                Forms\Components\DatePicker::make('expiration_date')
                    ->label('Fecha de Vencimiento')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->minDate(now()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('lot_number')
            ->columns([
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Bodega')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-building-storefront')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('lot_number')
                    ->label('Número de Lote')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Lote copiado')
                    ->icon('heroicon-o-tag')
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->sortable()
                    ->suffix(' und')
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state > 100 => 'success',
                        $state > 50 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray'
                    }),
                
                Tables\Columns\TextColumn::make('cost')
                    ->label('Costo Unit.')
                    ->money('COP')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('expiration_date')
                    ->label('Fecha de Vencimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->badge()
                    ->color(function ($state) {
                        if (!$state) return 'gray';
                        $daysUntilExpiration = now()->diffInDays($state, false);
                        if ($daysUntilExpiration < 0) return 'danger'; // Vencido
                        if ($daysUntilExpiration < 30) return 'danger'; // Próximo a vencer
                        if ($daysUntilExpiration < 90) return 'warning';
                        return 'success';
                    })
                    ->icon(function ($state) {
                        if (!$state) return null;
                        $daysUntilExpiration = now()->diffInDays($state, false);
                        if ($daysUntilExpiration < 0) return 'heroicon-o-x-circle';
                        if ($daysUntilExpiration < 30) return 'heroicon-o-exclamation-triangle';
                        if ($daysUntilExpiration < 90) return 'heroicon-o-clock';
                        return 'heroicon-o-check-circle';
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('expiration_date', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('location_id')
                    ->label('Bodega')
                    ->options(Location::where('business_id', auth()->user()->business_id)->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo Lote')
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        // El product_id se asigna automáticamente por la relación
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No hay lotes registrados')
            ->emptyStateDescription('Crea el primer lote para este producto usando el botón de arriba.')
            ->emptyStateIcon('heroicon-o-cube');
    }
}
