<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineGroup extends Model
{
    protected $fillable = [
        'line_id', 'group_id', 'day_of_week'
    ];
}
