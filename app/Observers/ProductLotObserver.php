<?php

namespace App\Observers;

use App\Models\ProductLot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductLotObserver
{
    /**
     * Handle the ProductLot "created" event.
     */
    public function created(ProductLot $productLot): void
    {
        $this->clearB2BCache($productLot);
        Log::info('Cache B2B limpiado después de crear lote', [
            'lot_id' => $productLot->id,
            'product_id' => $productLot->product_id,
            'location_id' => $productLot->location_id
        ]);
    }

    /**
     * Handle the ProductLot "updated" event.
     */
    public function updated(ProductLot $productLot): void
    {
        $this->clearB2BCache($productLot);
        Log::info('Cache B2B limpiado después de actualizar lote', [
            'lot_id' => $productLot->id,
            'product_id' => $productLot->product_id
        ]);
    }

    /**
     * Handle the ProductLot "deleted" event.
     */
    public function deleted(ProductLot $productLot): void
    {
        $this->clearB2BCache($productLot);
        Log::info('Cache B2B limpiado después de eliminar lote', [
            'lot_id' => $productLot->id,
            'product_id' => $productLot->product_id
        ]);
    }

    /**
     * Limpiar el caché de productos B2B
     */
    protected function clearB2BCache(ProductLot $productLot): void
    {
        // Limpiar el caché de productos para la bodega B2B
        $cacheKey = 'b2b_products_location_' . $productLot->location_id;
        Cache::forget($cacheKey);
        
        // También limpiar el caché de la API de búsqueda si existe
        Cache::forget('b2b_products_location_' . $productLot->location_id);
    }
}
