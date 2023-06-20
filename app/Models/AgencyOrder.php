<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgencyOrder extends Model
{
    //1 = Chua TT, 2 = Da TT, 4 = Huy don
    const STATUS_CHUA_KC = 1;
    const STATUS_DA_KC = 2;
    const STATUS_HUY_DON = 4;

    const ALL_STATUS = 0;

    const STATUS_TEXTS = [
        self::ALL_STATUS     => '- Tất cả trạng thái -',
        self::STATUS_CHUA_KC   => 'Chưa KC',
        self::STATUS_DA_KC   => 'Đã KC',
        self::STATUS_HUY_DON => 'Hủy đơn',
    ];

    const ALL_TYPE_AGENCY_ORDER = 0;
    const TYPE_AGENCY_ORDER     = 1; // đơn nhập đại lý
    const TYPE_TDV_ORDER        = 2; // trình duyệt viên lên đơn

    const TYPE_TEXTS = [
        self::ALL_TYPE_AGENCY_ORDER => "- Tất cả loại -",
        self::TYPE_AGENCY_ORDER     => "Đơn ĐL",
        self::TYPE_TDV_ORDER        => "TDV",
    ];

    protected $fillable = [
        'title',
        'booking_at',
        'total_amount',
        'agency_id',
        'agency_province_id',
        'agency_address',
        'note',
        'type',
        'status',
        'created_by',
        'updated_by',
        'order_code',
    ];

    function items()
    {
        return $this->belongsToMany(AgencyOrderItem::class);
    }

    function store_orders()
    {
        return $this->belongsToMany(StoreOrder::class, 'agency_order_store_order')->withPivot('agency_order_id', 'store_order_id');
    }

    public function agency()
    {
        return $this->hasOne(Agency::class, 'id', 'agency_id');
    }

    public function creator()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function agencyOrderItems()
    {
        return $this->hasMany(AgencyOrderItem::class, 'agency_order_id', 'id');
    }

    function getFullAddressAttribute(): string
    {
        $output = [
            $this->agency_address ?: '',
            $this->ward ? $this->ward->ward_name_with_type : '',
            $this->district ? $this->district->district_name_with_type : '',
            $this->province ? $this->province->province_name_with_type : ''
        ];

        return implode(', ', array_filter($output));
    }
}
