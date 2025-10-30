<?php

namespace App\Imports;

use App\Models\ProductLot;
use App\Models\Product;
use App\Models\Location;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log;

class LotsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    private $products;
    private $locations;
    private $businessId;

    public function __construct()
    {
        $this->businessId = auth()->user()->business_id;

        $this->products = Product::where('business_id', $this->businessId)
            ->pluck('id', 'sku')
            ->toArray();

        $this->locations = Location::where('business_id', $this->businessId)
            ->pluck('id', 'name')
            ->toArray();
    }

    public function model(array $row)
    {
        $productId = $this->products[$row['sku_producto']] ?? null;
        $locationId = $this->locations[$row['nombre_bodega']] ?? null;

        if (!$productId || !$locationId) {
            Log::warning('Importación de lote omitida: SKU o Bodega no encontrados', $row);
            return null;
        }

        $expirationDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_vencimiento'])->format('Y-m-d');

        return new ProductLot([
            'product_id'        => $productId,
            'location_id'       => $locationId,
            'lot_number'        => $row['numero_lote'],
            'expiration_date'   => $expirationDate,
            'quantity'          => $row['cantidad'],
            'cost'              => $row['costo'] ?? 0,
            'stock_minimo'      => $row['stock_minimo'] ?? 0,
        ]);
    }

    public function batchSize(): int { return 1000; }
    public function chunkSize(): int { return 1000; }
}