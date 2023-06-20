<?php

namespace App\Repositories\StoreChange;

use App\Repositories\BaseRepositoryInterface;

interface StoreChangeRepositoryInterface extends BaseRepositoryInterface
{
    public function getByRequest($paginate, $with = [], $requestParams = []);
}
