<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForgetCheckin extends Model
{
    const STATUS_REVIEW  = 1;
    const STATUS_APPROVE = 2;
    const STATUS_REJECT  = 3;

    const STATUS_TEXTS = [
        self::STATUS_REVIEW  => 'Chờ duyệt',
        self::STATUS_APPROVE => 'Chấp nhận',
        self::STATUS_REJECT  => 'Từ chối',
    ];

    protected $fillable = [
        'checkin_id',
        'creator_note',
        'reviewer_note',
        'created_by',
        'updated_by',
        'status'
    ];

    public function checkin()
    {
        return $this->hasOne(Checkin::class, 'id', 'checkin_id');
    }

    public function creator()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function reviewer()
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }
}
