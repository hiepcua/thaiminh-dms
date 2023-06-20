<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    const STATUS_ACTIVE   = 1;
    const STATUS_INACTIVE = 0;

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfStatus(\Illuminate\Database\Eloquent\Builder $query, $status)
    {
        return $query->where('status', $status);
    }

    function getStatusNameAttribute()
    {
        return $this->status == self::STATUS_ACTIVE ? 'Hoạt động' : 'Không HĐ';
    }
}
