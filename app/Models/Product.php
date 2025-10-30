<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'business_id',
        'category_id',
        'product_channel_id',
        'pharmaceutical_form_id',
        'product_type_id',
        'name',
        'sku',
        'unit_of_measure_id',
        'price',
        // 'cost' se ha movido a la tabla product_lots
        'molecule',
        'concentration',
        'commercial_presentation',
        'commercial_name',
        'laboratory',
        'cold_chain',
        'controlled',
        'barcode',
        'cum',
        'invima_registration',
        'atc_code',
        'is_active',
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'cold_chain' => 'boolean',
        'controlled' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Un producto ahora tiene muchos lotes.
     */
    public function productLots(): HasMany
    {
        return $this->hasMany(ProductLot::class);
    }

    /**
     * ACCESOR: Calcula el stock total sumando el stock de todos los lotes.
     */
    public function getTotalStockAttribute(): float
    {
        // Usamos 'quantity' que es la columna en la tabla product_lots
        return $this->productLots()->sum('quantity');
    }

    /**
     * Obtiene el stock en una bodega específica.
     */
    public function getStockInLocation(int $locationId): float
    {
        return $this->productLots()->where('location_id', $locationId)->sum('quantity');
    }

    /**
     * Un producto pertenece a un negocio.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Un producto pertenece a una categoría (Grupo Farmacológico).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Un producto tiene una unidad de medida base.
     */
    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }

    /**
     * Un producto tiene muchos movimientos de stock.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Un producto pertenece a un Tipo de Producto.
     */
    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * Un producto pertenece a un Canal de Producto.
     */
    public function productChannel(): BelongsTo
    {
        return $this->belongsTo(ProductChannel::class);
    }

    /**
     * Un producto pertenece a una Forma Farmacéutica.
     */
    public function pharmaceuticalForm(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalForm::class);
    }
}