<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StoreChange extends Model
{
    protected $fillable = [
        'store_id',
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
        'reason',
        'store_status',
    ];

    const ALL_STATUS         = 'all';
    const STATUS_ACTIVE      = 1;
    const STATUS_INACTIVE    = 2;
    const STATUS_NOT_APPROVE = 3;

    const STATUS_TEXTS = [
        self::ALL_STATUS         => 'Tất cả',
        self::STATUS_INACTIVE    => 'Chờ duyệt',
        self::STATUS_ACTIVE      => 'Đã duyệt',
        self::STATUS_NOT_APPROVE => 'Không duyệt',
    ];

    const REASON_INACTIVE_STORE_TEXT = 'Admin, SA dừng hoạt động nhà thuốc';

    static function checkExistStoreCode($code = null)
    {
        return self::query()->where('code', $code)->first();
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

    public function organization(): HasOne
    {
        return $this->hasOne(Organization::class, 'id', 'organization_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function userUpdate(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function lineStore(): HasOne
    {
        return $this->hasOne(LineStore::class, 'store_id', 'store_id');
    }

    public function store(): HasOne
    {
        return $this->hasOne(Store::class, 'id', 'store_id');
    }

    function getFullAddressAttribute(): string
    {
        $output = [
            $this->address ?: '',
            $this->ward ? $this->ward->ward_name_with_type : '',
            $this->district ? $this->district->district_name_with_type : '',
            $this->province ? $this->province->province_name_with_type : ''
        ];

        return implode(', ', array_filter($output));
    }

    function getVatInfo(): string
    {
        $output = [
            $this->vat_number ? '<b>MST</b>: ' . $this->vat_number : null,
            $this->vat_company ? '<b>Tên công ty</b>: ' . $this->vat_company : null,
            $this->vat_address ? '<b>Địa chỉ</b>: ' . $this->vat_address : null,
            $this->vat_buyer ? '<b>Người mua</b>: ' . $this->vat_buyer : null,
            $this->vat_email ? '<b>Email</b>: ' . $this->vat_email : null
        ];

        return implode(', ', array_filter($output));
    }
}
