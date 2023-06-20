<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'province_id',
        'address',
        'vat_buyer',
        'vat_company',
        'vat_address',
        'vat_number',
        'vat_email',
        'status',
        'order_code',
        'type_tax',
        'is_user',
        'created_by',
        'updated_by',
        'pay_number',
        'pay_service_cost',
        'pay_personal_tax',
    ];

    const STATUS_ACTIVE   = 1;
    const STATUS_INACTIVE = 0;

    const IS_USER   = 1;
    const ISNT_USER = 0;

    const STATUS_TEXTS = [
        self::STATUS_INACTIVE => 'Ngừng hoạt động',
        self::STATUS_ACTIVE   => 'Hoạt động',
    ];

    const TYPE_TAX_COMPANY = 'CT';
    const TYPE_TAX_PRIVATE = 'NB';

    const TYPE_TAX_TEXTS = [
        self::TYPE_TAX_PRIVATE => 'Nội bộ (NB)',
        self::TYPE_TAX_COMPANY => 'Công ty (CT)'
    ];

    const ATTRIBUTES_TEXT = [
        'code'        => 'Mã',
        'name'        => 'Tên',
        'address'     => 'Địa chỉ',
        'vat_buyer'   => 'Người mua hàng',
        'vat_company' => 'Tên công ty',
        'vat_address' => 'Địa chỉ',
        'vat_number'  => 'Mã số thuế',
        'vat_email'   => 'Email',
        'status'      => 'Trạng thái',
    ];

    function organizations()
    {
        return $this->belongsToMany(Organization::class, 'agency_organizations')->withPivot('agency_id', 'organization_id');
    }

    function localies()
    {
        return $this->belongsToMany(Organization::class, 'agency_organizations')
            ->withPivot('agency_id', 'organization_id')
            ->where('organizations.type', Organization::TYPE_DIA_BAN);
    }

    function division()
    {
        return $this->belongsToMany(Organization::class, 'agency_organizations')
            ->withPivot('agency_id', 'organization_id')
            ->where('organizations.type', Organization::TYPE_KHU_VUC);
    }

    public function province()
    {
        return $this->hasOne(Province::class, 'id', 'province_id');
    }

    public function reportAgencyInventory()
    {
        return $this->hasMany(ReportAgencyInventory::class, 'agency_id', 'id');
    }

    public function agencyOrder()
    {
        return $this->hasMany(AgencyOrder::class, 'agency_id', 'id');
    }

    public function storeOrder()
    {
        return $this->hasMany(StoreOrder::class, 'agency_id', 'id');
    }
}
