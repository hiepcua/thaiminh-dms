<?php

namespace App\Repositories\Promotion;

use App\Repositories\BaseRepositoryInterface;

interface PromotionRepositoryInterface extends BaseRepositoryInterface
{
    public function getByRequest($paginate, $with = [], $requestParams = []);
}
