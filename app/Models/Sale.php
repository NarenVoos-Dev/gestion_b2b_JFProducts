<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    use HasFactory;
    
    
    protected $fillable = [
        'business_id', 
        'client_id', 
        'date', 
        'subtotal', 
        'tax', 
        'total',
        'is_cash', 
        'status', 
        'pending_amount',
        'location_id', 
        'notes', 
        'source',
        'invoice_number',
        'invoice_pdf_path',
        'invoiced_at',
    ];
    
   
    protected $casts = [         
        'is_cash' => 'boolean',
        'date' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'invoiced_at' => 'datetime',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
    
    /**
     * Una venta pertenece a un negocio.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Una venta pertenece a un cliente.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Una venta tiene muchos items (productos).
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Una venta tiene un documento electrónico asociado.
     */
    public function electronicDocument()
    {
        return $this->hasOne(ElectronicDocument::class);
    }

    /**
     * Una venta B2B tiene una cuenta por cobrar asociada.
     */
    public function accountReceivable()
    {
        return $this->hasOne(AccountReceivable::class);
    }

    /**
     * Obtener URL de descarga de la factura PDF
     */
    public function getInvoicePdfUrl()
    {
        if ($this->invoice_pdf_path) {
            return \Storage::url($this->invoice_pdf_path);
        }
        return null;
    }

    /**
     * Verificar si tiene factura PDF subida
     */
    public function hasInvoicePdf(): bool
    {
        return !empty($this->invoice_pdf_path) && \Storage::disk('public')->exists($this->invoice_pdf_path);
    }

}
