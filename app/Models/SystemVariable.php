<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemVariable extends Model
{
    protected $fillable = [
        'function',
        'name',
        'display_name',
        'value'
    ];

    const DISTANCE_ALLOW_CHECKIN = 'distance_allow_checkin';
    const LIMIT_FORGET_CHECKIN_A_MONTH = 'limit_forget_checkin_a_month';
}
