<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jenssegers\Mongodb\Relations\HasOne;

class District extends Model
{
    protected $fillable = [
        'province_id',
        'district_name',
        'district_slug',
        'district_code',
        'district_type',
        'district_name_with_type',
    ];

    public function province(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Province::class, 'district_id', 'id');
    }

    public function ward(): HasMany
    {
        return $this->hasMany(Ward::class, 'district_id', 'id');
    }
}
