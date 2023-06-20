<?php

namespace App\Repositories\AgencyOrderFile;

use App\Repositories\BaseRepositoryInterface;

interface AgencyOrderFileRepositoryInterface extends BaseRepositoryInterface
{
    public function getDataForListScreen($requestParams, $showOption);
}
