<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'location_id',
        'inventory_entry_id',
        'lot_number',
        'expiration_date',
        'quantity',
        'cost',
        'is_active',
        //'stock_minimo',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'quantity' => 'decimal:2',
        'cost' => 'decimal:2',
        'is_active' => 'boolean',
        //'stock_minimo' => 'decimal:2',
    ];

    public function inventoryEntry(): BelongsTo
    {
        return $this->belongsTo(InventoryEntry::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
