<?php

namespace App\Models\Reports;

use Illuminate\Database\Eloquent\Model;

class SalesByClientView extends Model
{
    protected $table = 'v_sales_by_client';
    
    public $timestamps = false;
    
    protected $guarded = [];
    
    protected $casts = [
        'total_orders' => 'integer',
        'total_purchased' => 'decimal:2',
        'avg_order_value' => 'decimal:2',
        'last_purchase_date' => 'datetime',
        'first_purchase_date' => 'datetime',
        'days_since_last_purchase' => 'integer',
    ];
}
