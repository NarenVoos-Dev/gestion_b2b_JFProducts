<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItemLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_item_id',
        'product_lot_id',
        'quantity',
        'lot_number',
        'expiration_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'expiration_date' => 'date',
    ];

    // Relaciones
    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function productLot(): BelongsTo
    {
        return $this->belongsTo(ProductLot::class);
    }
}
