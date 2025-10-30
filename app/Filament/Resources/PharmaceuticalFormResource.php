<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PharmaceuticalFormResource\Pages;
use App\Filament\Resources\PharmaceuticalFormResource\RelationManagers;
use App\Models\PharmaceuticalForm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PharmaceuticalFormResource extends Resource
{
    protected static ?string $model = PharmaceuticalForm::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Configuracion'; // Agrupado en Configuración
    protected static ?string $modelLabel = 'Forma Farmacéutica';
    protected static ?string $pluralModelLabel = 'Formas Farmacéuticas';
    protected static ?int $navigationSort = 60;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('abbreviation')
                    ->label('Abreviatura (Ej: TAB, JBE)')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
               Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable(),
                Tables\Columns\TextColumn::make('abbreviation')->label('Abreviatura'),
            ])
            ->filters([
                //
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
            'index' => Pages\ListPharmaceuticalForms::route('/'),
            //'create' => Pages\CreatePharmaceuticalForm::route('/create'),
            //'edit' => Pages\EditPharmaceuticalForm::route('/{record}/edit'),
        ];
    }
}
