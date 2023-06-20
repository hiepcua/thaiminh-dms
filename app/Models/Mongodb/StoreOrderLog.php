<?php

namespace App\Models\Mongodb;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class StoreOrderLog extends Eloquent
{
    protected $connection = 'mongodb';
    protected $table = 'store_order_logs';
}
