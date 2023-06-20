<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkin extends Model
{
    protected $table = 'checkin';

    protected $fillable = [
        'store_id',
        'lng',
        'lat',
        'checkin_at',
        'checkout_at',
        'forget',
        'created_by',
        'updated_by'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function store()
    {
        return $this->hasOne(Store::class, 'id', 'store_id');
    }
}
