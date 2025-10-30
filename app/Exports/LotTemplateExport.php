<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LotTemplateExport implements WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array
    {
        // Estas son las columnas exactas que tu Importador espera
        return [
            'sku_producto',
            'nombre_bodega',
            'numero_lote',
            'fecha_vencimiento',
            'cantidad',
            'costo',
            'stock_minimo'
        ];
    }
}