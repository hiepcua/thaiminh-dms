<?php

namespace App\Repositories\Ward;

use App\Repositories\BaseRepositoryInterface;

interface WardRepositoryInterface extends BaseRepositoryInterface
{
    public function getByDistrict($districtId): array;
}
