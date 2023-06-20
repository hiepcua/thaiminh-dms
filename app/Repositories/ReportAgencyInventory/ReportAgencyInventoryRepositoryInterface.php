<?php

namespace App\Repositories\ReportAgencyInventory;

use App\Repositories\BaseRepositoryInterface;

interface ReportAgencyInventoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getAgencyInventoryMonth($year, $month, $agencyId = null);

    public function getDataForListScreen($requestParams, $showOption);

    public function getByAgencyIdsMonth($agencyIds, $with, $requestData);

    public function getInventory($agencyId, $productId, $month, $year);

    public function getLatestInventoryConfirmed($agencyId);
    }
