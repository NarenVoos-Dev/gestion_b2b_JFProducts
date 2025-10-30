<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'product_id',
        'location_id',
        'product_lot_id', // <<< AÑADIDO
        'type',
        'quantity',
        'reason',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    // <<< RELACIÓN AÑADIDA >>>
    public function productLot(): BelongsTo
    {
        return $this->belongsTo(ProductLot::class);
    }
}