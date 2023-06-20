<?php

namespace App\Repositories\AgencyOrder;

use App\Repositories\BaseRepositoryInterface;

interface AgencyOrderRepositoryInterface extends BaseRepositoryInterface
{
    public function getDataForListScreen(
        $with = [],
        $requestParams = [],
        $showOption = []
    );

    public function getQueryExportListScreen(
        $with = [],
        $requestParams = [],
        $showOption = []
    );

    public function getOrderCreatedByTDV($ids);

    public function getOrderRemoved($ids);

    public function getByAgencyOnMonth($agencyId, $month);

    public function getTDVOrder($from, $to, $agencyId = null);
}
