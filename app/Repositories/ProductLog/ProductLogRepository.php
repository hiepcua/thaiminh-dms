<?php

namespace App\Repositories\ProductLog;

use App\Models\ProductLog;
use App\Repositories\BaseRepository;

class ProductLogRepository extends BaseRepository implements ProductLogRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new ProductLog();
    }
}
