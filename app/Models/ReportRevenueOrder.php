<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportRevenueOrder extends Model
{
    protected $table = 'report_revenue_order';

    protected $fillable = [
        "id",
        "day",
        "period",
        "user_id",
        "store_id",
        "product_id",
        "product_group_id",
        "total_amount",
        "total_discount"
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function asm(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class, 'id', 'asm_user_id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function productGroup()
    {
        return $this->hasOne(ProductGroup::class, 'id', 'product_group_id');
    }

    public function productSubGroup(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductGroup::class, 'id', 'sub_group_id');
    }

    public function organization(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Organization::class, 'id', 'organization_id');
    }
}
