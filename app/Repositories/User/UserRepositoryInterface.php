<?php

namespace App\Repositories\User;

use App\Repositories\BaseRepositoryInterface;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function organizationExists(array $organization_ids): bool;

    public function getByRole($roleName);

    public function getByOrganizations(array $organization_ids, string $roleName = '', $with = []): \Illuminate\Database\Eloquent\Collection|array;

    public function changeAgency($agencyId, $userId = 0);

    function getByOrganization(array $organization_ids);
}
