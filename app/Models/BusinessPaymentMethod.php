<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessPaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'type',
        'bank_name',
        'account_type',
        'account_number',
        'account_holder',
        'qr_code_image',
        'payment_link',
        'description',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Relación con Business
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Scope para métodos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('display_order');
    }

    /**
     * Obtener label del tipo de método
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'bank_account' => 'Cuenta Bancaria',
            'qr_code' => 'Código QR',
            'payment_link' => 'Link de Pago',
            'cash' => 'Efectivo',
            'other' => 'Otro',
            default => $this->type,
        };
    }

    /**
     * Obtener label del tipo de cuenta
     */
    public function getAccountTypeLabel(): ?string
    {
        if (!$this->account_type) {
            return null;
        }

        return match($this->account_type) {
            'ahorros' => 'Ahorros',
            'corriente' => 'Corriente',
            default => $this->account_type,
        };
    }
}
