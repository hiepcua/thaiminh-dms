<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'code',
        'price',
        'created_by',
        'updated_by',
    ];

    const ATTRIBUTES_TEXT = [
        'code' => 'Mã',
        'name' => 'Tên',
        'price' => 'Giá tiền'
    ];

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
