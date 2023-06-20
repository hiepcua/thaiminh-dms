<?php

namespace App\Models;

class StoreRank extends BaseModel
{
    protected $table = 'store_ranks';

    protected $fillable = [
        'unique_key',
        'store_id',
        'from_date',
        'to_date',
        'group_id',
        'sub_group_id',
        'rank_id',
        'rank',
        'revenue',
        'priority',
        'rate',
        'bonus',
    ];

    const LIMIT_ROW_513 = 100;
    const FOLDER_CACHE_JSON_513 = '513_store_id';

    public function store()
    {
        return $this->hasOne(Store::class, 'id', 'store_id');
    }

    public function productGroup()
    {
        return $this->hasOne(ProductGroup::class, 'id', 'group_id');
    }

    public function subProductGroup()
    {
        return $this->hasOne(ProductGroup::class, 'id', 'sub_group_id');
    }
}
