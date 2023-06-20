<?php

namespace App\Repositories\RevenuePeriod;

use App\Repositories\BaseRepositoryInterface;

interface RevenuePeriodRepositoryInterface extends BaseRepositoryInterface
{
    public function deleteChildren(array $ids);

    public function getByRank($rankId, $date = null);

    public function getOldRevenue($requestData);
}
