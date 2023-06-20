<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'status',
        'created_by',
        'updated_by',
    ];

    const STATUS_ACTIVE   = '1';
    const STATUS_INACTIVE = '0';

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
}
