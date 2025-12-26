<?php

namespace App\Models\Reports;

use Illuminate\Database\Eloquent\Model;

class SalesByOrderView extends Model
{
    protected $table = 'v_sales_by_order';
    
    public $timestamps = false;
    
    // Este modelo es de solo lectura (apunta a una vista de BD)
    protected $guarded = [];
    
    protected $casts = [
        'sale_date' => 'datetime',
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'sale_total' => 'decimal:2',
    ];
}
