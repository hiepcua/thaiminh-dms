<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineStore extends Model
{
    protected $fillable = [
        'line_id', 'store_id', 'from', 'to', 'status', 'created_by', 'updated_by', 'number_visit', 'reference_type'
    ];

    const STATUS_ACTIVE               = 1;
    const STATUS_INACTIVE             = 2;
    const STATUS_PENDING              = 3;
    const STATUS_NOT_APPROVE          = 4;
    const STATUS_ALL                  = 'all';
    const STATUS_TEXTS                = [
        self::STATUS_INACTIVE    => 'Không hoạt động',
        self::STATUS_ACTIVE      => 'Đã duyệt',
        self::STATUS_PENDING     => 'Chờ duyệt',
        self::STATUS_NOT_APPROVE => 'Không duyệt',
    ];
    const DEFAULT_NUMBER_VISIT        = 1;
    const DEFAULT_REFERENCE_TYPE      = 'stores';
    const REFERENCE_TYPE_STORE_CHANGE = 'store_changes';
    const REFERENCE_TYPE_NEW_STORE    = 'new_stores';
    const REFERENCE_TYPE_STORE        = 'stores';
    const REFERENCE_TYPE_LINE         = 'lines';

    public function line(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Line::class, 'id', 'line_id');
    }

    public function store(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Store::class, 'id', 'store_id');
    }

    public function userCreatedBy(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function userUpdatedBy(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }
}
