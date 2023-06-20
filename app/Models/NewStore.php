<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NewStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'organization_id',
        'province_id',
        'district_id',
        'ward_id',
        'address',
        'phone_owner',
        'phone_web',
        'lng',
        'lat',
        'parent_id',
        'vat_parent',
        'vat_buyer',
        'vat_company',
        'vat_address',
        'vat_number',
        'vat_email',
        'line_day',
        'line_period',
        'note_private',
        'show_web',
        'file_id',
        'status',
        'created_by',
        'updated_by',
        'is_disabled',
        'store_id',
        'reason',
    ];

    const STATUS_ALL             = 'all';
    const STATUS_ACTIVE          = 1;
    const STATUS_INACTIVE        = 2;
    const STATUS_NOT_APPROVED    = 3;
    const STATUS_UN_DISABLE      = 0;
    const STATUS_IS_DISABLE      = 1;
    const STATUS_IS_DISABLE_TEXT = 'Đã xóa';

    const STATUS_TEXTS = [
        self::STATUS_ALL          => 'Tất cả',
        self::STATUS_ACTIVE       => 'Đã duyệt',
        self::STATUS_INACTIVE     => 'Chờ duyệt',
        self::STATUS_NOT_APPROVED => 'Không duyệt',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function userUpdate(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function organization(): HasOne
    {
        return $this->hasOne(Organization::class, 'id', 'organization_id');
    }

    public function parentStore(): HasOne
    {
        return $this->hasOne(Store::class, 'id', 'parent_id');
    }

    public function province(): HasOne
    {
        return $this->hasOne(Province::class, 'id', 'province_id');
    }

    public function district(): HasOne
    {
        return $this->hasOne(District::class, 'id', 'district_id');
    }

    public function ward(): HasOne
    {
        return $this->hasOne(Ward::class, 'id', 'ward_id');
    }

    function lineStore(): HasOne
    {
        return $this->hasOne(LineStore::class, 'store_id', 'id')
            ->where('reference_type', LineStore::REFERENCE_TYPE_NEW_STORE);
    }
}
