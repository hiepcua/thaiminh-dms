<?php

namespace App\Repositories\Store;

use App\Repositories\BaseRepositoryInterface;

interface StoreRepositoryInterface extends BaseRepositoryInterface
{
    public function paginate(int $limit, array $with = [], array $args = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    public function getList($province = null, $district = null, $name = null);

    public function checkStoreExist(array $attributes = []);

    public function getByCode($code = null);

    public function organizationExists(array $organization_ids): bool;

    public function getByLocality($locality_id = null);

    public function getByTdv($tdvId);
}
