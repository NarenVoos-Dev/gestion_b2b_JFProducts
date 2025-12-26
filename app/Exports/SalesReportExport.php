<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;
    protected $selectedColumns;
    protected $columnLabels;

    public function __construct($data, $selectedColumns, $columnLabels)
    {
        $this->data = $data;
        $this->selectedColumns = $selectedColumns;
        $this->columnLabels = $columnLabels;
    }

    public function collection()
    {
        // Convertir los datos a arrays con solo los valores en el orden correcto
        return $this->data->map(function($row) {
            $row = (array)$row;
            $orderedRow = [];
            
            // Mantener el orden de las columnas seleccionadas
            foreach ($this->selectedColumns as $column) {
                $value = $row[$column] ?? '';
                
                // Formatear número de pedido: PW + sale_id con padding
                if ($column === 'invoice_number') {
                    $saleId = $row['sale_id'] ?? null;
                    if ($saleId) {
                        $value = 'PW' . str_pad($saleId, 5, '0', STR_PAD_LEFT);
                    }
                }
                
                $orderedRow[] = $value;
            }
            
            return $orderedRow;
        });
    }

    public function headings(): array
    {
        // Retornar los nombres de las columnas seleccionadas
        return array_map(function($column) {
            return $this->columnLabels[$column] ?? $column;
        }, $this->selectedColumns);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la primera fila (encabezados)
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        // Ancho automático para todas las columnas
        $widths = [];
        foreach ($this->selectedColumns as $column) {
            $widths[chr(65 + count($widths))] = 20; // A, B, C, etc.
        }
        return $widths;
    }
}
