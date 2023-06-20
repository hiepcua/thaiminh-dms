<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $fillable = [
        'province_name',
        'province_slug',
        'province_code',
        'province_type',
        'province_name_with_type',
    ];

    public function district() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(District::class, 'province_id', 'id');
    }
    public function ward(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ward::class, 'province_id','id');
    }

    const HN_ID  = 1;
    const HCM_ID = 79;
}
