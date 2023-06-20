<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreOrderItem extends Model
{
    const PRODUCT_TYPE_PRODUCT  = 'product';
    const PRODUCT_TYPE_GIFT     = 'gift';
    const PRODUCT_TYPE_DISCOUNT = 'discount';

    public $timestamps = false;

    protected $fillable = [
        'store_order_id',
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
        'booking_at',
        'promo_id',
        'discount_detail',
    ];
    protected $casts = [
        'discount_detail' => 'array',
    ];

    function product(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function gift()
    {
        return $this->hasOne(Gift::class, 'id', 'product_id');
    }

    public function productGroup()
    {
        return $this->hasOne(ProductGroup::class, 'id', 'product_group_id');
    }
}
