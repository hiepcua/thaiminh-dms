<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenuePeriodItem extends Model
{
    protected $with = ['product_conditions'];
    public $timestamps = false;
    protected $fillable = [
        'revenue_period_id',
        'rank_id',
        'group_id',
        'sub_group_id',
        'revenue',
        'discount_rate',
        'priority_discount_rate',
        'priority_product_min',
    ];

    function product_conditions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RevenueProductCondition::class, 'revenue_period_item_id', 'id');
    }
}
