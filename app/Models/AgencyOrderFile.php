<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class AgencyOrderFile extends BaseModel
{
    protected $fillable = [
        'id',
	    'agency_order_id',
	    'agency_id',
	    'qty_store_order_merged',
	    'order_code',
	    'item_qty',
	    'cost', // tổng tiền chưa chiết khấu
	    'discount',
	    'final_cost', // Tổng tiền sau khi chiết khấu
        'file_url',
	    'created_at',
	    'updated_at',
    ];

    public function agencyOrder()
    {
        return $this->hasOne(AgencyOrder::class, 'id', 'agency_order_id');
    }

    public function agency()
    {
        return $this->hasOne(Agency::class, 'id', 'agency_id');
    }
}
