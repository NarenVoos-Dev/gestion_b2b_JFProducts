<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'price',
        'product_name',
        'image_url',
        'laboratory',
        'product_lot_id',
        'lot_number',
        'expiration_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'expiration_date' => 'date',
    ];

    /**
     * Un item del carrito pertenece a un usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un item del carrito pertenece a un producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Un item del carrito puede pertenecer a un lote específico (opcional)
     */
    public function productLot(): BelongsTo
    {
        return $this->belongsTo(ProductLot::class);
    }

    /**
     * Calcular el subtotal del item
     */
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->price;
    }
}
