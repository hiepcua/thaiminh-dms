<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    const STATUS_ACTIVE   = 1;
    const STATUS_INACTIVE = 0;

    const STATUS_TEXTS = [
        self::STATUS_ACTIVE   => 'Hoạt động',
        self::STATUS_INACTIVE => 'Ngừng hoạt động',
    ];

    const STATUS = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    const AUTO_APPLY    = 1;
    const NO_AUTO_APPLY = 2;

    const ATTRIBUTES_TEXT = [
        'name'            => 'Tên',
        'desc'            => 'Mô tả',
        'started_at'      => 'Ngày bắt đầu',
        'ended_at'        => 'Ngày kết thúc',
        'organization_id' => 'organization_id',
        'status'          => 'Trạng thái',
        'auto_apply'      => 'Tự động áp dụng',
    ];

    protected $fillable = [
        'name',
        'desc',
        'started_at',
        'ended_at',
        'organization_id',
        'auto_apply',
        'status',
        'created_by',
        'updated_by',
        'auto_apply',
        'max_gift'
    ];

    public function organizations()
    {
        return $this->belongsToMany(
            Organization::class,
            'organization_promotions',
            'promotion_id',
            'organization_id'
        )
            ->wherePivot('organization_promotions.type', OrganizationPromotion::TYPE_INCLUDE);
    }

    public function organizationPromotions()
    {
        return $this->hasMany(OrganizationPromotion::class, 'promotion_id', 'id');
    }

    public function divisions()
    {
        return $this->belongsToMany(
            Organization::class,
            'organization_promotions',
            'promotion_id',
            'organization_id'
        )
//            ->wherePivot('organization_promotions.type', OrganizationPromotion::TYPE_INCLUDE)
            ->where('organizations.type', '=', Organization::TYPE_KHU_VUC);
    }

    public function organizationsExclude()
    {
        return $this->belongsToMany(
            Organization::class,
            'organization_promotions',
            'promotion_id',
            'organization_id'
        )
            ->wherePivot('organization_promotions.type', OrganizationPromotion::TYPE_EXCLUDE);
    }

    public function createdBy()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function promotionConditions()
    {
        return $this->hasMany(PromotionCondition::class, 'promotion_id', 'id');
    }
}
