<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Relations\HasOne;

class Ward extends Model
{
    protected $fillable = [
        'province_id',
        'district_id',
        'ward_name',
        'ward_slug',
        'ward_code',
        'ward_type',
        'ward_name_with_type',
    ];

    public function province() : HasOne
    {
        return $this->hasOne(Province::class, 'id', 'province_id');
    }

    public function district() : HasOne
    {
        return $this->hasOne(District::class, 'id', 'district_id');
    }
}
