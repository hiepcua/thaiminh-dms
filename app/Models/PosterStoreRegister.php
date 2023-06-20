<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PosterStoreRegister extends Model
{
    use HasFactory;
    protected $fillable = [
        'store_id',
        'poster_id',
        'tdv_id',
        'status',
        'poster_height',
        'poster_width',
        'poster_area',
        'poster_note',
        'note',
        'title',
        'type',
        'offer_date',
        'offer_reason',
        'offer_reply',
    ];

    const IMAGE_POSTER = 'image_poster';
    const IMAGE_ACCEPTANCE = 'image_acceptance';

    const STATUS_ACTIVE   = 1;
    const STATUS_DEACTIVE = 0;

    const STATUS = [
        self::STATUS_ACTIVE   => 'Đã duyệt',
        self::STATUS_DEACTIVE => 'Đợi duyệt',
    ];
    const TYPE_IMAGE_REG = 0;
    const TYPE_IMAGE_POSTER = 1;
    const TYPE_IMAGE_ACCEPTANCE = 2;
    const TYPE_IMAGE_OFFER = 3;

    const TYPE = [
        self::TYPE_IMAGE_REG   => 'Chưa có ảnh',
        self::TYPE_IMAGE_POSTER   => 'Ảnh treo',
        self::TYPE_IMAGE_ACCEPTANCE => 'Ảnh Nghiệm thu',
        self::TYPE_IMAGE_OFFER => 'Đề xuất',
    ];


    public function store(): HasOne
    {
        return $this->hasOne(Store::class, 'id', 'store_id');
    }

    public function poster(): HasOne
    {
        return $this->hasOne(Poster::class, 'id', 'poster_id');
    }

    public function images()
    {
        $this->hasMany(File::class, 'attachment_id', 'id');
    }
    public function tdv()
    {
        return $this->hasOne(User::class, 'id', 'tdv_id');
    }

    public function getNameStatus()
    {
        return self::STATUS[$this->status] ?? '';
    }
}
