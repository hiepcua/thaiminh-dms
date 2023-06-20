<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends BaseModel
{
    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'key_id',
        'desc',
        'company_id',
        'wholesale_price',
        'price',
        'status',
        'point',
        'created_by',
        'updated_by',
        'unit',
        'display_name'
    ];
    const COMPANIES = ['1' => 'Thái Minh', '2' => 'We can', '3' => 'Hổ Cáp'];

    const STATUS_ACTIVE   = '1';
    const STATUS_INACTIVE = '0';
    const ALL_STATUS      = '-1';

    const STATUS_TEXTS = [
        self::ALL_STATUS      => 'Trạng thái',
        self::STATUS_ACTIVE   => 'Hoạt động',
        self::STATUS_INACTIVE => 'Không hoạt động',
    ];

    const UNIT_LO = 1;
    const UNIT_HOP = 2;
    const UNIT_TUYP = 3;

    const UNIT_TEXTS = [
        self::UNIT_LO => 'Lọ',
        self::UNIT_HOP => 'Hộp',
        self::UNIT_TUYP => 'Tuýp',
    ];

    public function bm_users()
    {
        return $this->belongsToMany(User::class, 'product_bm')->withPivot('product_id', 'user_id');
    }

    public function key_product()
    {
        return $this->hasOne(Product::class, 'id', 'key_id');
    }

    public function log(): HasMany
    {
        return $this->hasMany(ProductLog::class, 'product_id', 'id');
    }

    function getCompanyById(int $company_id)
    {
        return self::COMPANIES[$company_id] ?? '';
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function activeProductGroupPriority()
    {
        $currentDate = now()->format('Y-m-d');

        return $this->hasOne(ProductGroupPriority::class, 'product_id', 'id')
            ->where('product_group_priorities.status', '=', ProductGroupPriority::STATUS_ACTIVE)
            ->where('period_from', '<=', $currentDate . " 00:00:00")
            ->where(function ($q) use ($currentDate) {
                return $q->whereNull('period_to')
                    ->orWhere('period_to', '>=', $currentDate . " 23:59:59");
            });
    }

    public function productGroupPriorities()
    {
        return $this->hasMany(ProductGroupPriority::class, 'product_id', 'id');
    }
}
