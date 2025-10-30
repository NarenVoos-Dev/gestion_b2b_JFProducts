<?php

namespace App\Filament\Imports;

use App\Models\ProductLot;
use App\Imports\LotsImport; // <<< Importamos la lógica que ya creamos
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class LotsImporter extends Importer
{
    protected static ?string $model = ProductLot::class;

    // Aquí le decimos a Filament que use nuestra clase de importación de Maatwebsite
    public static function getImportClass(): string
    {
        return LotsImport::class;
    }

    // Aquí definimos las columnas que el usuario verá al subir el archivo,
    // y cómo se conectan con las cabeceras de tu Excel.
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('sku_producto')
                ->requiredMapping()
                ->label('SKU del Producto'),
            ImportColumn::make('nombre_bodega')
                ->requiredMapping()
                ->label('Nombre de Bodega'),
            ImportColumn::make('numero_lote')
                ->requiredMapping()
                ->label('Número de Lote'),
            ImportColumn::make('fecha_vencimiento')
                ->requiredMapping()
                ->label('Fecha de Vencimiento'),
            ImportColumn::make('cantidad')
                ->requiredMapping()
                ->numeric()
                ->label('Cantidad'),
            ImportColumn::make('costo')
                ->numeric()
                ->label('Costo'),
            ImportColumn::make('stock_minimo')
                ->numeric()
                ->label('Stock Mínimo'),
        ];
    }

    public function resolveRecord(): ?ProductLot
    {
        // Esta función es necesaria, pero nuestra lógica principal
        // de 'updateOrCreate' está en la clase LotsImport, así que
        // podemos simplemente devolver un nuevo objeto.
         return new ProductLot();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Se han importado ' . $import->successful_rows . ' lotes exitosamente.';
        if ($import->failed_rows > 0) {
            $body .= ' ' . $import->failed_rows . ' filas fallaron al importar.';
        }
        return $body;
    }
}