<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    protected $fillable = [
        'sale_id', 
        'product_id',
        'product_lot_id',  // NUEVO
        'lot_number',      // NUEVO
        'expiration_date', // NUEVO
        'quantity', 
        'price', 
        'tax_rate', 
        'unit_of_measure_id'  
    ];

    protected $casts = [
        'expiration_date' => 'date',  // NUEVO
    ];

    
    /**
     * Un item de venta pertenece a un Producto.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Un item de venta pertenece a una Venta.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Un item de venta tiene una unidad de medida.
     */
    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }

    /**
     * Un item de venta pertenece a un lote de producto.
     */
    public function productLot(): BelongsTo
    {
        return $this->belongsTo(ProductLot::class);
    }

    /**
     * Un item de venta puede tener múltiples lotes asignados.
     */
    public function lots()
    {
        return $this->hasMany(SaleItemLot::class);
    }

    /**
     * Verificar si el item tiene todos los lotes asignados.
     */
    public function hasAllLotsAssigned(): bool
    {
        // Si usa el sistema antiguo (product_lot_id directo)
        if ($this->product_lot_id) {
            return true;
        }
        
        // Si usa el sistema nuevo (tabla intermedia)
        $totalAssigned = $this->lots()->sum('quantity');
        return $totalAssigned >= $this->quantity;
    }

    /**
     * Obtener la cantidad total asignada desde los lotes.
     */
    public function getTotalQuantityFromLots(): float
    {
        return (float) $this->lots()->sum('quantity');
    }

    /**
     * Obtener la cantidad pendiente de asignar.
     */
    public function getRemainingQuantity(): float
    {
        return $this->quantity - $this->getTotalQuantityFromLots();
    }

}
