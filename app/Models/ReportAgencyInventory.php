<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportAgencyInventory extends Model
{
    const STATUS_KC     = 1;
    const STATUS_NOT_KC = 2;

    const STATUS_TEXTS = [
        self::STATUS_KC     => 'Đã kết chuyển',
        self::STATUS_NOT_KC => 'Chưa kết chuyển',
    ];

    const STATUS_INVENTORY_ENOUGH = 1;
    const STATUS_INVENTORY_NOT_ENOUGH = 2;

    const STATUS_INVENTORY_TEXTS = [
        self::STATUS_INVENTORY_ENOUGH     => 'Đủ hàng',
        self::STATUS_INVENTORY_NOT_ENOUGH => 'Thiếu hàng',
    ];


    protected $table = 'report_agency_inventory';

    protected $fillable = [
        'year',
        'month',
        'agency_id',
        'product_id',
        'start_num',
        'import_num',
        'export_num',
        'inventory_num',
        'inventory_real_num',
        'inventory_real_input_date',
        'status',
    ];

    public function agency()
    {
        return $this->hasOne(Agency::class, 'id', 'agency_id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
