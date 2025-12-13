<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountReceivable extends Model
{
    use HasFactory;

    protected $table = 'accounts_receivable';

    protected $fillable = [
        'sale_id',
        'client_id',
        'invoice_number',
        'amount',
        'balance',
        'due_date',
        'status',
        'notes',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'due_date' => 'date',
        'cancelled_at' => 'datetime',
    ];

    // Relaciones
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(AccountPayment::class);
    }

    // Métodos auxiliares
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->due_date < now() && $this->status !== 'paid';
    }

    public function getRemainingBalance(): float
    {
        return (float) $this->balance;
    }
    
    public function getTotalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }
}
