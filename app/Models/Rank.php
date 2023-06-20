<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    protected $fillable = [
        'name',
        'desc',
        'status',
        'created_by',
        'updated_by',
    ];

    const ALL_STATUS      = 0;
    const STATUS_ACTIVE   = 1;
    const STATUS_INACTIVE = 2;

    const STATUS_TEXT = [
        self::ALL_STATUS      => '-Trạng thái-',
        self::STATUS_ACTIVE   => 'Hoạt động',
        self::STATUS_INACTIVE => 'Không hoạt động',
    ];

    function getStatusNameAttribute()
    {
        if ($this->status == self::ALL_STATUS) return '';

        return self::STATUS_TEXT[$this->status] ?? '';
    }
}
