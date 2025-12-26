<?php

namespace App\Models\Reports;

use Illuminate\Database\Eloquent\Model;

class SalesByPeriodView extends Model
{
    protected $table = 'v_sales_by_period';
    
    public $timestamps = false;
    
    protected $guarded = [];
    
    protected $casts = [
        'sale_date' => 'datetime',
        'sale_day' => 'date',
        'sale_year' => 'integer',
        'sale_month' => 'integer',
        'sale_day_of_month' => 'integer',
        'sale_week' => 'integer',
        'sale_quarter' => 'integer',
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'sale_total' => 'decimal:2',
    ];
}
