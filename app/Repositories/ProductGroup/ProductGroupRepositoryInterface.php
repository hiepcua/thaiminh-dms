<?php

namespace App\Repositories\ProductGroup;

use App\Repositories\BaseRepositoryInterface;

interface ProductGroupRepositoryInterface extends BaseRepositoryInterface
{
    public function getGroupByType($productType = null): \Illuminate\Database\Eloquent\Collection|array;

    public function getSubGroupActive();
}
