<?php

namespace App\Repositories\ReportRevenueOrder;

use App\Repositories\BaseRepositoryInterface;

interface ReportRevenueOrderRepositoryInterface extends BaseRepositoryInterface
{
    public function getByUser($userId, $month);

    public function getTotalAmountByGroup($month);

    public function getMonthTotalAmount($userId, $month);

    public function getByDateRange($dateFrom, $dateTo, $storeId, $currentUserId);

    public function getRevenueTDVSummary(array $searchParams);

    public function getRevenueTDVDetail(array $searchParams);
}
