<?php

namespace App\Models;


use App\Helpers\Helper;
use Illuminate\Support\Carbon;

class StoreOrder extends BaseModel
{
    protected $fillable = [
        'user_id',
        'organization_id',
        'agency_id',
        'code',
        'booking_at',
        'delivery_at',
        'discount',
        'sub_total',
        'total_amount',
        'store_id',
        'store_province_id',
        'store_district_id',
        'store_ward_id',
        'paid',
        'note',
        'status',
        'created_by',
        'updated_by',
        'agency_order_id',
        'order_code',
        'agency_status',
        'order_type',
        'order_logistic',
        'order_logistic_type',
        'parent_id',
        'bravo_code',
        'ttk_product_type',
    ];

    const ORDER_TYPE_DON_THUONG       = 1;
    const ORDER_TYPE_DON_TTKEY        = 2;
    const ORDER_TYPE_TEXTS            = [
        self::ORDER_TYPE_DON_THUONG => 'Đơn thường',
        self::ORDER_TYPE_DON_TTKEY  => 'Đơn trả thưởng KEY',
    ];
    const ORDER_LOGISTIC_VIETTEL      = 1;
    const ORDER_LOGISTIC_TYPE_VIETTEL = 'viettel';
    const ORDER_LOGISTIC_TYPE_TDV     = 'tdv';

    const ALL_STATUS       = 0;
    const STATUS_CHUA_GIAO = 1;
    const STATUS_DA_GIAO   = 2;
    const STATUS_DA_HUY    = 3;
    const STATUS_TRA_LAI   = 4;
    const STATUS_DA_DAT    = 5;
    const STATUS_DA_XOA    = 6;
    const STATUS_TEXTS     = [
        self::ALL_STATUS       => '- Trạng thái -',
        self::STATUS_CHUA_GIAO => 'Chưa giao',
        self::STATUS_DA_GIAO   => 'Đã giao',
//        self::STATUS_DA_HUY    => 'Đã hủy',
//        self::STATUS_TRA_LAI   => 'Trả lại',
//        self::STATUS_DA_DAT    => 'Đã đặt',
//        self::STATUS_DA_XOA    => 'Đã xóa',
    ];

    const ALL_AGENCY_STATUS             = 0;
    const AGENCY_STATUS_DA_THANH_TOAN   = 1;
    const AGENCY_STATUS_CHUA_THANH_TOAN = 2;

    const AGENCY_STATUS_TEXT = [
        self::ALL_AGENCY_STATUS             => '- Đại lý thanh toán -',
        self::AGENCY_STATUS_DA_THANH_TOAN   => 'Đã TT',
        self::AGENCY_STATUS_CHUA_THANH_TOAN => 'Chưa TT',
    ];

    function organization(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Organization::class, 'id', 'organization_id');
    }

    function store(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Store::class, 'id', 'store_id');
    }

    function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StoreOrderItem::class, 'store_order_id', 'id');
    }

    function agency()
    {
        return $this->hasOne(Agency::class, 'id', 'agency_id');
    }

    function sale()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    function getFullAddressAttribute(): string
    {
        $output = [
            $this->ward ? $this->ward->ward_name_with_type : '',
            $this->district ? $this->district->district_name_with_type : '',
            $this->province ? $this->province->province_name_with_type : ''
        ];

        return implode(', ', array_filter($output));
    }

    public function ward()
    {
        return $this->hasOne(Ward::class, 'id', 'store_ward_id');
    }

    public function district()
    {
        return $this->hasOne(District::class, 'id', 'store_district_id');
    }

    public function province()
    {
        return $this->hasOne(Province::class, 'id', 'store_province_id');
    }

    public function getStatusNameAttribute()
    {
        if ($this->status == self::ALL_STATUS)
            return '';

        return self::STATUS_TEXTS[$this->status] ?? '';
    }

    public function getBookingDeliveryTextAttribute(): string
    {
        $dates = [
            Carbon::parse($this->booking_at)->format('d/m/Y'),
            $this->delivery_at ? Carbon::create($this->delivery_at)->format('d/m/Y') : '',
        ];
        $dates = array_filter($dates);
        $dates = array_unique($dates);
        return implode('<br>', $dates);
    }

    public function getListProductAttribute()
    {
        $products = [];
        $this->items->filter(function ($item) {
            return in_array($item->product_type, ['product', 'gift']);
        })
            ->groupBy('product_type')
            ->each(function ($items) use (&$products) {
                foreach ($items as $item) {
                    $key = $item->product_type . '_' . $item->product_id;
                    if (empty($products[$key])) {
                        $default = [
                            'type'          => $item->product_type,
                            'code'          => ($item->product_type == 'product' && $item->product ? $item->product->code : $item->product_name),
                            'qty'           => $item->product_qty,
                            'price'         => $item->product_price,
                            'price_format'  => Helper::formatPrice($item->product_price, ''),
                            'amount'        => '',
                            'amount_format' => '',
                        ];
                        if ($item->product_type == 'product') {
                            $default['amount']        = $item->product_qty * $item->product_price;
                            $default['amount_format'] = Helper::formatPrice($default['amount'], '');
                        }
                        $products[$key] = $default;
                    } else {
                        $products[$key]['qty'] += $item->product_qty;
                        if ($item->product_type == 'product') {
                            $products[$key]['amount']        = $products[$key]['qty'] * $products[$key]['price'];
                            $products[$key]['amount_format'] = Helper::formatPrice($products[$key]['amount'], '');
                        }
                    }
                }
            });
        return $products;
    }

    public function agencyOrder()
    {
        return $this->hasOne(AgencyOrder::class, 'id', 'agency_order_id');
    }
}
