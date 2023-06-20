<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueProductCondition extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'revenue_period_item_id',
        'product_id',
        'min_box',
    ];
}
