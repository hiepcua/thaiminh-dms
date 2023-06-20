<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\Agency;
use App\Repositories\ReportRevenueOrder\ReportRevenueOrderRepositoryInterface;
use App\Repositories\StoreOrder\StoreOrderRepositoryInterface;
use Carbon\Carbon;

class DashboardService extends BaseService
{
    protected $storeOrderRepository;
    protected $reportRevenueOrderRepository;

    public function __construct(
        StoreOrderRepositoryInterface $storeOrderRepository,
        ReportRevenueOrderRepositoryInterface $reportRevenueOrderRepository
    ){
        parent::__construct();

        $this->storeOrderRepository = $storeOrderRepository;
        $this->reportRevenueOrderRepository = $reportRevenueOrderRepository;
    }

    public function setModel()
    {
//        return new Agency();
    }


    public function formOptions($model = null): array
    {
        $yearRangeCanSearch = $this->storeOrderRepository->getYearRangeOfUser(Helper::currentUser()->id);

        $yearCanSearch = [];

        for ($i = $yearRangeCanSearch['min']; $i <= $yearRangeCanSearch['max']; $i++) {
            $yearCanSearch[$i] = $i;
        }

        $currentTime = Carbon::now();

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = $i;
        }

        $month = request('search.month', $currentTime->month);
        $year  = request('search.year', $currentTime->year);

        return [
            'default' => ['month' => $month, 'year' => $year,],
            'years'   => $yearCanSearch,
            'months'  => $months,
        ];
    }

    public function getDataForTdv($params)
    {
        $currentTime = Carbon::now();

        $month        = $params['month'] ?? $currentTime->month;
        $year         = $params['year'] ?? $currentTime->year;
        $monthText    = str_pad($month ?? '', 2, "0", STR_PAD_LEFT);
        $agoMonth     = $month == 1 ? 12 : ($month - 1);
        $agoMonthText = str_pad($agoMonth, 2, "0", STR_PAD_LEFT);
        $agoYear      = $month == 1 ? ($year - 1) : $year;

        $totalAmountByGroup = $this->reportRevenueOrderRepository->getByDateRange(
            "$year-$monthText-01",
            "$year-$monthText-31"
        );

        $newTotalAmountByGroup = [];
        foreach ($totalAmountByGroup as $amountByGroup) {
            if (isset($newTotalAmountByGroup[$amountByGroup->product_group_id])) {
                $newTotalAmountByGroup[$amountByGroup->product_group_id]->month_total_amount += $amountByGroup->sum_total_amount;
            } else {
                $amountByGroup->month_total_amount = $amountByGroup->sum_total_amount;
                $newTotalAmountByGroup[$amountByGroup->product_group_id] = $amountByGroup;
            }
        }

        $totalAmountByGroup = $newTotalAmountByGroup;

        $totalAmountCurrentMonth = $this->reportRevenueOrderRepository->getMonthTotalAmount(
            Helper::currentUser()->id,
            "$year-$monthText"
        );
        $totalAmountAgoMonth     = $this->reportRevenueOrderRepository->getMonthTotalAmount(
            Helper::currentUser()->id,
            "$agoYear-$agoMonthText"
        );
        $percentAgoMonthText = '';
        if ($totalAmountAgoMonth) {
            $percent = $totalAmountCurrentMonth / $totalAmountAgoMonth;
            $percent                 = $percent == 0
                ? 0
                : number_format($percent * 100, 2, ',');
            $totalAmountAgoMonth     = Helper::formatPrice($totalAmountAgoMonth) . 'đ';
            $percentAgoMonthText     = "$percent% tháng $agoMonth ($totalAmountAgoMonth)";
        }
        $reportRevenueOrders = $this->reportRevenueOrderRepository
            ->getByUser(Helper::currentUser()->id, "$year-$monthText")
            ->toArray();
        $orders = [];
        foreach ($reportRevenueOrders as $order) {
            if (isset($order['product_group']) && isset($order['product'])) {
                $orders[$order['product_group']['name']]['item'][] = [
                    'name' => $order['product']['name'] ?? '',
                    'month_total_amount' => $order['month_total_amount'] ?? ''
                ];

                if (isset($orders[$order['product_group']['name']]['month_total_amount'])) {
                    $orders[$order['product_group']['name']]['month_total_amount'] += $order['month_total_amount'];
                } else {
                    $orders[$order['product_group']['name']]['month_total_amount'] = $order['month_total_amount'];
                }
            }
        }

        return [
            'totalAmountCurrentMonth' => $totalAmountCurrentMonth,
            'percentAgoMonthText'     => $percentAgoMonthText,
            'orders'                  => $orders,
            'totalAmountByGroup'      => $totalAmountByGroup,
        ];
    }
}
