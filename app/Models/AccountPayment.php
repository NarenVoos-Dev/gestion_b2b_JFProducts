<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AccountPayment extends Model
{
    use HasFactory;

    protected $table = 'account_payments';

    protected $fillable = [
        'account_receivable_id',
        'amount',
        'payment_method',
        'payment_date',
        'reference',
        'notes',
        'payment_proof_path',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // Relaciones
    public function accountReceivable(): BelongsTo
    {
        return $this->belongsTo(AccountReceivable::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // Métodos helper para comprobante de pago
    public function hasProof(): bool
    {
        return !empty($this->payment_proof_path) && Storage::disk('local')->exists($this->payment_proof_path);
    }
    
    public function getProofUrl(): ?string
    {
        if (!$this->hasProof()) {
            return null;
        }
        
        return route('payment.proof.download', ['payment' => $this->id]);
    }
}
