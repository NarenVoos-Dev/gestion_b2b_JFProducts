<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryEntry extends Model
{
    protected $fillable = [
        'business_id', 'user_id', 'location_id', 'supplier_id', 
        'city', 'reference', 'entry_date', 'notes'
    ];

    // Un ingreso PUEDE TENER MUCHOS lotes de producto
    public function productLots(): HasMany
    {
        return $this->hasMany(ProductLot::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}