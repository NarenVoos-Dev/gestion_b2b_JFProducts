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
        'lot_expiration_date',
        'expiration_date',
        'extension_count',
        'extension_requested_at',
        'extension_status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'lot_expiration_date' => 'date',
        'expiration_date' => 'datetime',
        'extension_requested_at' => 'datetime',
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
    
    /**
     * Verificar si el item ha expirado
     */
    public function isExpired(): bool
    {
        return $this->expiration_date && $this->expiration_date->isPast();
    }
    
    /**
     * Verificar si puede solicitar prórroga
     */
    public function canRequestExtension(): bool
    {
        return $this->extension_count < 3 && 
               $this->extension_status !== 'pending';
    }
    
    /**
     * Verificar si está por expirar (menos de 30 minutos)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expiration_date) return false;
        
        return $this->expiration_date->diffInMinutes(now()) <= 30 &&
               !$this->isExpired();
    }
}
