<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Store extends Model
{
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
        'status',
        'created_by',
        'updated_by',
        'file_id',
    ];

    const STATUS_ACTIVE        = 1;
    const STATUS_INACTIVE      = 0;
    const STATUS_ACTIVE_TEXT   = 'yes';
    const STATUS_INACTIVE_TEXT = 'no';
    const SHOW_WEB             = 1;

    const STATUS_TEXTS = [
        self::STATUS_INACTIVE => 'Không hoạt động',
        self::STATUS_ACTIVE   => 'Hoạt động',
    ];

    const STORE_TYPE_LE    = 1;
    const STORE_TYPE_CHUOI = 2;
    const STORE_TYPE_CHO   = 3;
    const STORE_TYPE       = [
        self::STORE_TYPE_LE    => 'Lẻ',
        self::STORE_TYPE_CHUOI => 'Chuỗi',
        self::STORE_TYPE_CHO   => 'Chợ',
    ];

    const TYPE_MARKET   = 3;
    const PREFIX_MARKET = 'CHO';

    const LINE_DAY = [
        '1' => 'Thứ 2',
        '2' => 'Thứ 3',
        '3' => 'Thứ 4',
        '4' => 'Thứ 5',
        '5' => 'Thứ 6',
        '6' => 'Thứ 7',
    ];

    const LINE_PERIOD = [
        '1' => '1 lần',
        '2' => '2 lần',
        '3' => '3 lần',
        '4' => '4 lần',
    ];

    const USE_VAT_PARENT = 1;

    const ATTRIBUTES_TEXT = [
        'name'            => 'Tên',
        'code'            => 'Mã',
        'type'            => 'Loại',
        'organization_id' => 'Địa bàn',
        'province_id'     => 'Tỉnh/ Thành phố',
        'district_id'     => 'Quận/Huyện',
        'ward_id'         => 'Phường/Xã',
        'address'         => 'Địa chỉ',
        'phone_owner'     => 'SĐT',
        'phone_web'       => 'SĐT điểm bán',
        'lng'             => 'Kinh độ',
        'lat'             => 'Vĩ độ',
        'parent_id'       => 'Nhà thuốc cha',
        'vat_parent'      => 'Viết hóa đơn về nhà thuốc cha',
        'vat_buyer'       => 'Người mua hàng',
        'vat_company'     => 'Tên công ty',
        'vat_address'     => 'Địa chỉ',
        'vat_number'      => 'Mã số thuế',
        'vat_email'       => 'Email',
        'line_day'        => 'Thứ',
        'line_period'     => 'Chu kỳ',
        'note_private'    => 'Ghi chú nội bộ',
        'show_web'        => 'Hiển thị điểm bán',
        'status'          => 'Trạng thái',
    ];

    function getStatusNameAttribute()
    {
        return $this->status == self::STATUS_ACTIVE ? 'Hoạt động' : 'Ngừng HĐ';
    }

    function files()
    {
        return $this->belongsToMany(File::class, 'store_file')->withPivot('store_id', 'file_id');
    }

    function line()
    {
        return $this->belongsToMany(Line::class, 'line_stores')->withPivot('store_id', 'line_id');
    }

    public function getNumberVisit()
    {
        return $this->line()
                ->select('number_visit')
                ->where('line_stores.status', LineStore::STATUS_ACTIVE)
                ->first()?->number_visit;
    }

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

    public function storeRank()
    {
        return $this->hasMany(StoreRank::class, 'store_id', 'id');
    }

    public function storeOrders()
    {
        return $this->hasMany(StoreOrder::class, 'store_id', 'id');
    }

    public function store_parent()
    {
        return $this->hasOne(self::class, 'id', 'parent_id');
    }

    public function checkins()
    {
        return $this->hasMany(Checkin::class, 'store_id', 'id');
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

    public function getParentCodeAndName(): string
    {
        $output = [
            $this->store_parent ? $this->store_parent->code : '',
            $this->store_parent ? $this->store_parent->name : ''
        ];

        return implode(' - ', array_filter($output));
    }

    public function getCodeAndName(): string
    {
        $output = [
            $this->code ?? null,
            $this->name ?? null
        ];

        return implode(' - ', array_filter($output));
    }

    public function storeChanges(): HasMany
    {
        return $this->hasMany(StoreChange::class, 'store_id', 'id');
    }

    public function creator(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function reportRevenueStores(): HasMany
    {
        return $this->hasMany(ReportRevenueStore::class, 'store_id', 'id');
    }
}
