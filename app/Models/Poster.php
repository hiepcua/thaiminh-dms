<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Poster extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'description',
        'product_id',
        'start_date',
        'end_date',
        'reward_month',
        'reward_amount',
        'acceptance_date',
        'status',
        'image',
    ];

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function organizations()
    {
        return $this->belongsToMany(
            Organization::class,
            'poster_organizations',
            'poster_id',
            'organization_id'
        );
    }

    public function accptance_date_list()
    {
        {
            return $this->hasMany(PosterAcceptanceDate::class, 'poster_id', 'id');
        }
    }

    public function posterRegisted()
    {
        {
            return $this->hasMany(PosterStoreRegister::class, 'poster_id', 'id');
        }
    }

}
