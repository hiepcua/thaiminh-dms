<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jenssegers\Mongodb\Relations\HasOne;

class Line extends Model
{
    protected $fillable = [
        'name', 'organization_id', 'created_by', 'updated_by', 'status'
    ];

    const STARTYEAR = 2022;

    const ALL_DAY  = 'all';
    const WEEKDAYS = [
        1 => 'Thứ 2',
        2 => 'Thứ 3',
        3 => 'Thứ 4',
        4 => 'Thứ 5',
        5 => 'Thứ 6',
        6 => 'Thứ 7',
    ];

    const ALL_DAY_OF_WEEK = 'all';

    const MONTHS = [
        1  => 'Tháng 1',
        2  => 'Tháng 2',
        3  => 'Tháng 3',
        4  => 'Tháng 4',
        5  => 'Tháng 5',
        6  => 'Tháng 6',
        7  => 'Tháng 7',
        8  => 'Tháng 8',
        9  => 'Tháng 9',
        10 => 'Tháng 10',
        11 => 'Tháng 11',
        12 => 'Tháng 12',
    ];

    const STATUS_ACTIVE   = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_TEXTS    = [
        self::STATUS_INACTIVE => 'Không hoạt động',
        self::STATUS_ACTIVE   => 'Hoạt động',
    ];

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'line_stores')->withPivot('id', 'line_id', 'store_id', 'from', 'to', 'number_visit', 'status', 'created_by', 'updated_by');
    }

    public function storesRunning(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'line_stores')
            ->where('line_stores.to', null)
            ->where('line_stores.status', LineStore::STATUS_ACTIVE);
    }

    public function productGroup(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ProductGroup::class, 'line_groups', 'line_id', 'group_id')->withPivot(
            'id', 'line_id', 'group_id', 'day_of_week'
        );
    }

    public function lineGroup(): HasMany
    {
        return $this->hasMany(LineGroup::class, 'line_id', 'id');
    }

    public function organizations(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Organization::class, 'id', 'organization_id');
    }

    public function lineStores()
    {
        return $this->hasMany(LineStore::class, 'line_id', 'id')
            ->whereIn('line_stores.reference_type', [LineStore::REFERENCE_TYPE_LINE, LineStore::DEFAULT_REFERENCE_TYPE]);
    }
}
