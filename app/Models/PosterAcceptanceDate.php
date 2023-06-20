<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosterAcceptanceDate extends Model
{
    use HasFactory;
    protected $fillable = [
        'poster_id',
        'acceptance_start_date',
        'acceptance_end_date',
    ];
}
