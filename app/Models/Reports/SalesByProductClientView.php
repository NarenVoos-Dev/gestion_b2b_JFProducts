<?php

namespace App\Models\Reports;

use Illuminate\Database\Eloquent\Model;

class SalesByProductClientView extends Model
{
    protected $table = 'v_sales_by_product_client';
    
    public $timestamps = false;
    
    protected $guarded = [];
    
    protected $casts = [
        'times_purchased' => 'integer',
        'total_quantity' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'avg_price' => 'decimal:2',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'last_purchase_date' => 'datetime',
        'first_purchase_date' => 'datetime',
    ];
}
