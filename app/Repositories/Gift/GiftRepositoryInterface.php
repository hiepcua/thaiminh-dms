<?php

namespace App\Repositories\Gift;

use App\Repositories\BaseRepositoryInterface;

interface GiftRepositoryInterface extends BaseRepositoryInterface
{
    public function getByRequest($paginate, $with = [], $requestParams = []);
}
