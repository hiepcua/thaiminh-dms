<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class ProductGroupPriority extends Model
{
    const PRIORITY        = 1;
    const NOPRIORITY      = 0;
    const STATUS_ACTIVE   = 1;
    const STATUS_INACTIVE = 0;
    const PERIOD_FOREVER  = 'maimai';

    const STATUS_TEXTS = [
        self::STATUS_INACTIVE => 'Ngừng hoạt động',
        self::STATUS_ACTIVE   => 'Hoạt động',
    ];

    const PRIORITY_TEXTS = [
        self::NOPRIORITY => 'Không',
        self::PRIORITY   => 'Có',
    ];

    const STORE_TYPE_RETAIL      = 1;
    const STORE_TYPE_CHAIN_STORE = 2;
    const STORE_TYPE_MARKET      = 3;

    const STORE_TYPE_TEXTS = [
        self::STORE_TYPE_RETAIL      => 'Lẻ',
        self::STORE_TYPE_CHAIN_STORE => 'Chuỗi',
        self::STORE_TYPE_MARKET      => 'Chợ',
    ];

    const REGION_APPLY_NORTH    = 1;
    const REGION_APPLY_SOUTHERN = 2;
    const REGION_APPLY_CENTRAL  = 3;
    const REGION_APPLY_ALL      = 6;

    const REGION_APPLY_TEXTS = [
        self::REGION_APPLY_NORTH    => 'Miền bắc',
        self::REGION_APPLY_CENTRAL  => 'Miền trung',
        self::REGION_APPLY_SOUTHERN => 'Miền nam',
        self::REGION_APPLY_ALL      => 'Toàn quốc',
    ];

    const MONTH_PERIOD = [
        '1'  => '1',
        '2'  => '1',
        '3'  => '2',
        '4'  => '2',
        '5'  => '3',
        '6'  => '3',
        '7'  => '4',
        '8'  => '4',
        '9'  => '5',
        '10' => '5',
        '11' => '6',
        '12' => '6',
    ];

    protected $fillable = [
        'product_id',
        'group_id',
        'sub_group_id',
        'priority',
        'period_from',
        'period_to',
        'status',
        'created_by',
        'updated_by',
        'product_type',
        'store_type',
        'region_apply'
    ];

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function productSubGroup(): HasOne
    {
        return $this->hasOne(ProductGroup::class, 'id', 'sub_group_id');
    }

    public function productGroup(): HasOne
    {
        return $this->hasOne(ProductGroup::class, 'id', 'group_id')
            ->where('product_groups.status', ProductGroup::STATUS_ACTIVE);
    }
}
