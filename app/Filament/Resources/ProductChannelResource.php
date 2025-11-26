<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\ProductConfiguration;
use App\Filament\Resources\ProductChannelResource\Pages;
use App\Filament\Resources\ProductChannelResource\RelationManagers;
use App\Models\ProductChannel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductChannelResource extends Resource
{
    protected static ?string $model = ProductChannel::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $cluster = ProductConfiguration::class;
    protected static ?string $modelLabel = 'Canal de Producto';
    protected static ?string $pluralModelLabel = 'Canales de Producto';
    protected static ?string $navigationLabel = 'Canales de Producto';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del Canal (Ej: Institucional, Comercial)')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListProductChannels::route('/'),
            //'create' => Pages\CreateProductChannel::route('/create'),
            //'edit' => Pages\EditProductChannel::route('/{record}/edit'),
        ];
    }
}
