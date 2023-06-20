<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductGroup extends BaseModel
{
    const STATUS_ACTIVE   = 1;
    const STATUS_INACTIVE = 0;

    const STATUS_TEXTS = [
        self::STATUS_INACTIVE => 'Không hoạt động',
        self::STATUS_ACTIVE   => 'Hoạt động',
    ];

    const PRODUCT_TYPE_QC          = 1;
    const PRODUCT_TYPE_TV          = 2;
    const PRODUCT_TYPE_MARKET      = 4;
    const PRODUCT_TYPE_CHAIN_STORE = 5;
    const PRODUCT_TYPE_MARKET_QC   = 6;

    const PRODUCT_TYPES = [
        self::PRODUCT_TYPE_QC          => [
            'text'           => 'Quảng cáo',
            'period_of_year' => 6,
            'code'           => 'QC',
        ],
        self::PRODUCT_TYPE_TV          => [
            'text'           => 'Tư vấn',
            'period_of_year' => 12,
            'code'           => 'TV',
        ],
        self::PRODUCT_TYPE_MARKET      => [
            'text'           => 'Chợ',
            'period_of_year' => 12,
        ],
        self::PRODUCT_TYPE_CHAIN_STORE => [
            'text'           => 'Chuỗi',
            'period_of_year' => 6,
        ],
        self::PRODUCT_TYPE_MARKET_QC   => [
            'text'           => 'Chợ Quảng cáo',
            'period_of_year' => 12,
        ],
//        3 => [
//            'text' => 'Độc quyền',
//            'period_of_year' => 6
//        ],
    ];

    protected $fillable = [
        'name', 'parent_id', 'product_type', 'status', 'note', 'created_by', 'updated_by',
    ];

    function parent()
    {
        return $this->belongsTo(ProductGroup::class);
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    function getParentNameAttribute()
    {
        return $this->parent ? $this->parent->name : '';
    }

    static function sortLists(int $id = 0, int $level = 0, \Illuminate\Support\Collection $lists = null, $status = true): \Illuminate\Support\Collection
    {
        if (is_null($lists)) {
            if ($status) {
                $lists = self::query()->ofStatus(self::STATUS_ACTIVE)->get();
            } else {
                $lists = self::query()->get();
            }
        }
        $results = collect([]);
        $items   = $lists->where('parent_id', $id);
        foreach ($items as $_item) {
            $_item->level = $level;
            $results->add($_item);
            $children = self::sortLists($_item->id, $level + 1, $lists);
            if ($children->isNotEmpty()) {
                $results = $results->merge($children);
            }
        }

        return $results;
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_group_priorities',
            'group_id',
            'product_id',
            'id',
            'id'
        )
            ->where('product_group_priorities.status', ProductGroupPriority::STATUS_ACTIVE)
            ->where('product_group_priorities.period_from', '>=', now()->format('Y-m-d H:i:s'))
            ->where('product_group_priorities.period_to', '<=', now()->format('Y-m-d H:i:s'));
    }
}
