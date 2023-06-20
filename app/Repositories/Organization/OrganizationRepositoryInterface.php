<?php

namespace App\Repositories\Organization;

use App\Repositories\BaseRepositoryInterface;

interface OrganizationRepositoryInterface extends BaseRepositoryInterface
{
    public function updateStatus(array $ids, $status): void;

    public function getDivisionsActive();

    public function getLocalityActive();

    public function getLocalityByDivision($divisionId);
}
