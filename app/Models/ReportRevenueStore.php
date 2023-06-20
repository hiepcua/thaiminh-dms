<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportRevenueStore extends Model
{
    protected $table = 'report_revenue_store';

    protected $fillable = [
        "id",
        "day",
        "store_id",
        "total_order",
        "total_product",
        "total_sub_amount",
        "total_discount",
        "total_amount",
    ];
    public function pharmacy()
    {
        return $this->hasOne(Store::class, 'id', 'store_id');
    }
}
