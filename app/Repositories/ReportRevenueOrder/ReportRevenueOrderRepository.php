<?php

namespace App\Repositories\ReportRevenueOrder;

use App\Helpers\Helper;
use App\Models\ReportRevenueOrder;
use App\Repositories\BaseRepository;

class ReportRevenueOrderRepository extends BaseRepository implements ReportRevenueOrderRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new ReportRevenueOrder();
    }

    public function getByUser($userId, $month)
    {
        return $this->model
            ->with(['user', 'product', 'productGroup'])
            ->selectRaw('product_id, product_group_id, SUM(total_amount) as month_total_amount')
            ->where('user_id', $userId)
            ->where('day', '>=', "$month-01")
            ->where('day', '<=', "$month-31")
            ->groupBy('product_group_id', 'product_id')
            ->get();
    }

    public function getMonthTotalAmount($userId, $month)
    {
        $order = $this->model->where('user_id', $userId)
            ->where('product_id', '!=', 0)
            ->where('product_group_id', '!=', 0)
            ->where('day', '>=', "$month-01")
            ->where('day', '<=', "$month-31")
            ->get();

        return $order->sum('total_amount');
    }

    public function getTotalAmountByGroup($month)
    {
        return $this->model
            ->with(['productGroup'])
            ->selectRaw('product_group_id, SUM(total_amount) as month_total_amount')
            ->where('product_id', '!=', 0)
            ->where('product_group_id', '!=', 0)
            ->where('day', '>=', "$month-01")
            ->where('day', '<=', "$month-31")
            ->groupBy('product_group_id')
            ->get();
    }

    public function getByDateRange($dateFrom, $dateTo, $storeId = null, $currentUserId = null)
    {
        return $this->model
            ->with(['productGroup', 'product', 'user'])
            ->selectRaw('product_group_id, product_id, user_id, SUM(total_amount) as sum_total_amount')
            ->where('product_id', '!=', 0)
            ->where('product_group_id', '!=', 0)
            ->where('day', '>=', $dateFrom)
            ->where('day', '<=', $dateTo)
            ->groupBy('product_group_id', 'product_id', 'user_id')
            ->when(isset($storeId), function ($q) use ($storeId) {
                return $q->where('store_id', $storeId);
            })
            ->when(isset($currentUserId), function ($q) use ($currentUserId) {
                return $q->where('user_id', $currentUserId);
            })
            ->get();
    }

    public function getRevenueTDVSummary(array $searchParams)
    {
        $query_results = $this->model::query()
            ->with(['user', 'asm', 'productGroup', 'productSubGroup', 'organization'])
            ->select(['asm_user_id', 'user_id', 'product_group_id', 'sub_group_id', 'organization_id'])
            ->selectRaw('SUM(total_amount) AS amount')
            ->where('product_group_id', '!=', 0)
            ->where('asm_user_id', '!=', 0)
            ->whereBetween('day', [$searchParams['fromDate'], $searchParams['toDate']])
            ->when($searchParams['locality_id'], function ($q) use ($searchParams) {
                $q->whereIn('organization_id', $searchParams['locality_id']);
            })
            ->when($searchParams['user_id'], function ($q) use ($searchParams) {
                $q->where('user_id', $searchParams['user_id']);
            })
            ->groupBy('asm_user_id', 'user_id', 'product_group_id', 'sub_group_id', 'organization_id')
            ->orderBy('organization_id')
            ->orderBy('asm_user_id')
            ->orderBy('user_id')
            ->orderBy('product_group_id')
            ->get();
        $results       = $groupIds = [];
        foreach ($query_results as $item) {
//            $results[$item->asm_user_id][$item->product_group_id][$item->user_id][$item->organization_id][$item->sub_group_id] = $item;
            $results[$item->asm_user_id][$item->user_id][$item->organization_id][] = $item;

            $groupIds[$item->product_group_id] = $item->product_group_id;
            $groupIds[$item->sub_group_id]     = $item->sub_group_id;
        }
        return ['results' => $results, 'group_ids' => $groupIds];
    }

    public function getRevenueTDVDetail(array $searchParams)
    {
        $query_results = $this->model::query()
            ->with(['user', 'product',])
            ->select(['user_id', 'product_id', 'total_discount'])
            ->selectRaw('SUM(total_quantity) AS qty, SUM(total_amount) AS revenue')
            ->whereBetween('day', [$searchParams['fromDate'], $searchParams['toDate']])
            ->when($searchParams['user_id'] ?? 0, function ($query) use ($searchParams) {
                $query->where('user_id', $searchParams['user_id']);
            })
            ->when($searchParams['asm_user_id'] ?? 0, function ($query) use ($searchParams) {
                $query->where('asm_user_id', $searchParams['asm_user_id']);
            })
            ->groupBy('user_id', 'product_id')
            ->get()
            ->map(function ($item) {
                $item->total_discount = Helper::formatPrice($item->total_discount);
                $item->product_value  = [
                    'name'  => $item->product->name ?? '',
                    'price' => $item->product->wholesale_price ?? 0,
                ];
                $item->tdv_value      = $item->user->username ?? '';
                return $item;
            });

        $products = $query_results->pluck('product_value', 'product_id')->unique()->sortBy('name')->toArray();
        $users    = $query_results->pluck('tdv_value', 'user_id')->unique()->toArray();
        $results  = $query_results->groupBy('product_id')
            ->map(function ($items) {
                return $items->keyBy('user_id')->toArray();
            });

        return compact('products', 'users', 'results');
    }

    public function getLastOrderByTDV($tdvId = null, $storeId = null)
    {
        return $this->model
            ->where('user_id', $tdvId)
            ->where('store_id', $storeId)
            ->max('day');
    }
}
