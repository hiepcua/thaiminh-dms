<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StoreHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'store_id',
        'store_data',
        'created_by',
        'updated_by',
    ];

    public function store() : HasOne
    {
        return $this->hasOne(Store::class, 'id', 'store_id');
    }
}
