<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\ProductConfiguration;
use App\Filament\Resources\MoleculeResource\Pages;
use App\Filament\Resources\MoleculeResource\RelationManagers;
use App\Models\Molecule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MoleculeResource extends Resource
{
    protected static ?string $model = Molecule::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $cluster = ProductConfiguration::class;
    protected static ?string $modelLabel = 'Molécula';
    protected static ?string $pluralModelLabel = 'Moléculas';
    protected static ?string $navigationLabel = 'Moléculas';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre de la Molécula')
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
            'index' => Pages\ListMolecules::route('/'),
            //'create' => Pages\CreateMolecule::route('/create'),
            //'edit' => Pages\EditMolecule::route('/{record}/edit'),
        ];
    }
}
