<?php

namespace App\Repositories\Agency;

use App\Repositories\BaseRepositoryInterface;

interface AgencyRepositoryInterface extends BaseRepositoryInterface
{
    public function getDivision($agencyId);

    public function getLocalities($agencyId);

    public function getByRequest($paginate, $with, $requestParams);

    public function getByLocality($locality);

    public function getDataForListScreen(
        $with = [],
        $withCount = [],
        $requestParams = [],
        $showOption = []
    );

    public function organizationExists(array $organization_ids): bool;

    public function checkCodeExists(string $code): bool;

    public function getByPrefixCode(string $code): \Illuminate\Database\Eloquent\Collection|array;

    public function getInventoryHistory($dataSearch, $with, $showOption);
}
