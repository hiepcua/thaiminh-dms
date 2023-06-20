<?php

namespace App\Repositories\StoreOrder;

use App\Repositories\BaseRepositoryInterface;

interface StoreOrderRepositoryInterface extends BaseRepositoryInterface
{
    public function checkCodeExists($code): bool;

    public function getYearRangeOfUser($userId);

    public function getMonthTotalAmount($userId, $month);

    public function getTurnoverOfStore($storeId, $from, $to);

    public function getQueryForStatementAgency($requestParams);

    public function getDataForStatementAgency($requestParams, $showOption);

    public function getQueryExportListScreen(
        $with = [],
        $requestParams = [],
        $showOption = []
    );

    public function getProductInOrderList($requestParams);

    public function getDeliveryOrder($from, $to, $agencyId = null);

    public function getOrderTTKey($storeId, $productType, $status = null): array;

    public function countOrderChild($parentId): int;

    public function getLastOrderOfUser($userIds, $from, $to);
}
