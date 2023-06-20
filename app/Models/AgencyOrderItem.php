<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgencyOrderItem extends Model
{
    const PRODUCT_TYPE_PRODUCT = 'product';
    const PRODUCT_TYPE_GIFT = 'gift';
    const PRODUCT_TYPE_DISCOUNT = 'discount';

    protected $fillable = [
        'agency_order_id',
        'product_type',
        'product_id',
        'product_group_id',
        'product_sub_group_id',
        'product_priority',
        'product_name',
        'product_price',
        'product_qty',
        'discount',
        'sub_total',
        'total_amount',
        'note',
        'created_by'
    ];

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function gift()
    {
        return $this->hasOne(Gift::class, 'id', 'product_id');
    }

    public function creator()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
}
