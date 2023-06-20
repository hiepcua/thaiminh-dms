<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionCondition extends Model
{
    const TYPE_DISCOUNT_VND     = 1;
    const TYPE_DISCOUNT_PERCENT = 2;

    const TYPE_DISCOUNT_TEXTS = [
        self::TYPE_DISCOUNT_VND     => "VNĐ",
        self::TYPE_DISCOUNT_PERCENT => "%",
    ];

    const TYPE_GIFT_BY_QTY             = 1; //tang qua theo so luong
    const TYPE_GIFT_BY_TOTAL_QTY       = 2; //tang qua theo tong so luong
    const TYPE_GIFT_BY_TOTAL_COST      = 3; //tang qua theo tong tien
    const TYPE_DISCOUNT_BY_QTY         = 4; //chiet khau theo so luong
    const TYPE_DISCOUNT_BY_TOTAL_QTY   = 5; //chiet khau theo tong so luong
    const TYPE_DISCOUNT_BY_TOTAL_COST  = 6; //chiet khau theo tong tien
    const TYPE_DISCOUNT_BY_TOTAL_POINT = 7; //chiet khau theo tong diem
    const TYPE_GIFT_BY_TOTAL_POINT     = 8; //tang qua theo tong diem

    const TYPE_TEXTS = [
        self::TYPE_GIFT_BY_QTY             => 'Tặng quà SL',
        self::TYPE_GIFT_BY_TOTAL_QTY       => 'Tặng quà TSL',
        self::TYPE_GIFT_BY_TOTAL_COST      => 'Tặng quà TT',
        self::TYPE_GIFT_BY_TOTAL_POINT     => 'Tặng quà TĐ',
        self::TYPE_DISCOUNT_BY_QTY         => 'Chiết khấu SL',
        self::TYPE_DISCOUNT_BY_TOTAL_QTY   => 'Chiếu khấu TSL',
        self::TYPE_DISCOUNT_BY_TOTAL_COST  => 'Chiết khấu TT',
        self::TYPE_DISCOUNT_BY_TOTAL_POINT => 'Chiết khấu TĐ',
    ];

    const TYPES = [
        self::TYPE_GIFT_BY_QTY,
        self::TYPE_GIFT_BY_TOTAL_QTY,
        self::TYPE_GIFT_BY_TOTAL_COST,
        self::TYPE_GIFT_BY_TOTAL_POINT,
        self::TYPE_DISCOUNT_BY_QTY,
        self::TYPE_DISCOUNT_BY_TOTAL_QTY,
        self::TYPE_DISCOUNT_BY_TOTAL_COST,
        self::TYPE_DISCOUNT_BY_TOTAL_POINT,
    ];

    protected $fillable = [
        'promotion_id',
        'name',
        'type',
        'condition',
    ];
}
