<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = ['mime_type', 'name', 'source', 'created_by', 'disk_name', 'attachment_type', 'attachment_id', 'field'];
}
