<?php

namespace App\Models;

class OrganizationPromotion extends BaseModel
{
    protected $fillable = [
        'id',
        'organization_id',
        'promotion_id',
        'created_at',
        'updated_at',
        'type',
    ];

    const TYPE_INCLUDE = 1;
    const TYPE_EXCLUDE = 2;
}
