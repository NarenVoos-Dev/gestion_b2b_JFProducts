<?php
namespace App\Filament\Resources;

use App\Filament\Clusters\ProductConfiguration;
use App\Filament\Resources\CommercialNameResource\Pages;
use App\Models\CommercialName;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CommercialNameResource extends Resource
{
    protected static ?string $model = CommercialName::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $cluster = ProductConfiguration::class;
    protected static ?string $modelLabel = 'Nombre Comercial';
    protected static ?string $pluralModelLabel = 'Nombres Comerciales';
    protected static ?string $navigationLabel = 'Nombres Comerciales';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre Comercial')
                    ->required()
                    ->unique(ignoreRecord: true)
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
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommercialNames::route('/'),
            //'create' => Pages\CreateCommercialName::route('/create'),
            //'edit' => Pages\EditCommercialName::route('/{record}/edit'),
        ];
    }    
}