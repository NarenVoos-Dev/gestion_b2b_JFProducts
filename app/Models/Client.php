<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Client extends Model
{
    use HasFactory;
    use \App\Traits\HasCities;

    protected $fillable = [
        'business_id',
        'name',
        'type_document', // <<-- NUEVO CAMPO
        'document',
        'email',
        'phone1',        // <<-- NUEVO CAMPO
        'phone2',        // <<-- NUEVO CAMPO
        'address',
        'city_id', // <<-- NUEVO CAMPO
        'credit_limit',
        'price_list_id',
        'is_active',     // <<-- NUEVO CAMPO
    ];

    protected $casts = [
        'is_active' => 'boolean', // <<-- CAST A BOOLEAN
        'credit_limit' => 'decimal:2',
    ];

    // Relaciones

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
    
    public function sales()
    {
        return $this->hasMany(\App\Models\Sale::class);
    }
    public function user() {
        return $this->hasOne(\App\Models\User::class); 
    }

    /**
     * Calcula la deuda actual del cliente sumando el saldo pendiente de cuentas por cobrar.
     */
    public function getCurrentDebt(): float
    {
        return \App\Models\AccountReceivable::where('client_id', $this->id)
            ->where('status', '!=', 'paid')
            ->sum('balance');
    }

    // Lógica para verificar si puede comprar a crédito
    public function canPurchaseOnCredit($amount)
    {
        if ($this->credit_limit <= 0) {
            return [
                'can_purchase' => false,
                'reason' => 'Cliente sin límite de crédito asignado'
            ];
        }
        
        $currentDebt = $this->getCurrentDebt();
        $newDebt = $currentDebt + $amount;
        
        if ($newDebt <= $this->credit_limit) {
            return [
                'can_purchase' => true,
                'current_debt' => $currentDebt,
                'available_credit' => $this->credit_limit - $currentDebt
            ];
        } else {
            return [
                'can_purchase' => false,
                'reason' => 'Excede el límite de crédito',
                'current_debt' => $currentDebt,
                'credit_limit' => $this->credit_limit,
                'excess_amount' => $newDebt - $this->credit_limit
            ];
        }
    }

    // Obtener estadísticas de crédito (Mantenido)
    public function getCreditStats()
    {
        $currentDebt = $this->getCurrentDebt();
        $availableCredit = max(0, $this->credit_limit - $currentDebt);
        $creditUtilization = $this->credit_limit > 0 ? ($currentDebt / $this->credit_limit) * 100 : 0;

        return [
            'credit_limit' => $this->credit_limit,
            'current_debt' => $currentDebt,
            'available_credit' => $availableCredit,
            'credit_utilization_percentage' => round($creditUtilization, 2),
            'is_over_limit' => $currentDebt > $this->credit_limit
        ];
    }
    public function getCityNameAttribute()
    {
        return $this->city_id ? self::getCityNameById($this->city_id) : null;
    }
}
