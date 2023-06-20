<?php

namespace App\Models;

class RevenuePeriod extends BaseModel
{
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

    const STATUS_ACTIVE = 1;

    protected $fillable = [
        'rank_id',
        'period_from',
        'period_to',
        'product_type',
        'status',
        'created_by',
        'updated_by',
        'store_type',
        'region_apply',
    ];

    function rank(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Rank::class, 'id', 'rank_id');
    }

    function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RevenuePeriodItem::class, 'revenue_period_id', 'id');
    }

    public function scopeActive($query, $date = null)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->when(isset($date), function ($q) use ($date) {
                return $q->where('period_from', '<=', $date)
                    ->where('period_to', '>=', $date);
            });
    }
}
