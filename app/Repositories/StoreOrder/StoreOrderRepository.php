<?php

namespace App\Repositories\StoreOrder;

use App\Helpers\Helper;
use App\Models\Organization;
use App\Models\StoreOrder;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use function Clue\StreamFilter\fun;

class StoreOrderRepository extends BaseRepository implements StoreOrderRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new StoreOrder();
    }

    public function checkCodeExists($code): bool
    {
        return StoreOrder::query()->select('id')->where('code', $code)->exists();
    }

    public function getByRequest($paginate, $with = [], $requestParams = [])
    {
        $locality_ids = $requestParams['locality_ids'] ?? [];

        return $this->model->with($with)
            ->when(($requestParams['store_name'] ?? '') || ($requestParams['store_code'] ?? ''), function (Builder $query) use ($requestParams) {
                $query->whereHas('store', function (Builder $q) use ($requestParams) {
                    if ($requestParams['store_code'] ?? []) {
                        $q->whereIn('code', $requestParams['store_code']);
                    } elseif ($requestParams['store_name'] ?? '') {
                        $q->where('name', 'REGEXP', '[[:<:]]' . $requestParams['store_name'] . '[[:>:]]');
                    }
                });
            })
            ->when($requestParams['code'] ?? [], function (Builder $query) use ($requestParams) {
                $query->whereIn('code', $requestParams['code']);
            })
            ->when($requestParams['status'] ?? '', function (Builder $query) use ($requestParams) {
                $query->whereIn('status', (array)$requestParams['status']);
            })
            ->when($requestParams['created_by'] ?? '', function (Builder $query) use ($requestParams) {
                $query->where('created_by', (int)$requestParams['created_by']);
            })
            ->when(isset($requestParams['booking_at']), function (Builder $query) use ($requestParams) {
                if ($requestParams['booking_at']['from']) {
                    $query->where('booking_at', '>=', $requestParams['booking_at']['from']);
                }
                if ($requestParams['booking_at']['to']) {
                    $query->where('booking_at', '<=', $requestParams['booking_at']['to']);
                }
            })
            ->when($locality_ids, function (Builder $query) use ($locality_ids) {
                $query->whereIn('organization_id', $locality_ids);
            })
            ->when(isset($requestParams['parent_id']), function (Builder $query) use ($requestParams) {
                $query->where('parent_id', $requestParams['parent_id']);
            })
            ->when(isset($requestParams['order_type']), function (Builder $query) use ($requestParams) {
                $query->where('order_type', $requestParams['order_type']);
            })
            ->orderByDesc('booking_at')
            ->orderByDesc('id')
            ->paginate($paginate);
    }

    protected function makeQueryBySearchParam($with = [], $requestParams = [])
    {
        $currentUser               = Helper::currentUser();
        $organizationOfCurrentUser = Helper::getUserOrganization($currentUser);

        return $this->model
            ->with($with)
            ->leftJoin('agencies', 'agencies.id', '=', 'store_orders.agency_id')
            ->leftJoin('provinces', 'provinces.id', '=', 'store_orders.store_province_id')
            ->leftJoin('districts', 'districts.id', '=', 'store_orders.store_district_id')
            ->leftJoin('wards', 'wards.id', '=', 'store_orders.store_ward_id')
            ->selectRaw("
                store_orders.*,
                agencies.name as agency_name,
                provinces.province_name_with_type,
                districts.district_name_with_type,
                wards.ward_name_with_type
            ")
            ->when(isset($requestParams['codeOrName']), function ($query1) use ($requestParams) {
                return $query1->where('agencies.code', $requestParams['codeOrName'])
                    ->orWhere('agencies.name', 'like', '%' . $requestParams['codeOrName'] . '%');
            })
            ->when(isset($requestParams['agencyCode']), function ($query1) use ($requestParams) {
                return $query1->where('store_orders.order_code', $requestParams['agencyCode']);
            })
            ->whereHas('agency', function ($q) use ($requestParams, $currentUser, $organizationOfCurrentUser) {
                return $q->whereHas('organizations', function ($query2) use ($requestParams, $currentUser, $organizationOfCurrentUser) {
                    return $query2->when(isset($requestParams['division_id']), function ($query3) use ($requestParams) {
                        return $query3->where('parent_id', $requestParams['division_id'])
                            ->where('organizations.type', '=', Organization::TYPE_DIA_BAN);
                    })->when($currentUser->can('loc_du_lieu_cay_so_do') && count($organizationOfCurrentUser), function ($q3) use ($organizationOfCurrentUser) {
                        return $q3->whereIn('organizations.id', $organizationOfCurrentUser[Organization::TYPE_DIA_BAN]);
                    });
                });
            })
            ->when(isset($requestParams['booking_at']), function ($query) use ($requestParams) {
                return $query->when(isset($requestParams['booking_at']['from']), function ($query1) use ($requestParams) {
                    return $query1->where('store_orders.booking_at', '>=', $requestParams['booking_at']['from']);
                })
                    ->when(isset($requestParams['booking_at']['to']), function ($query1) use ($requestParams) {
                        return $query1->where('store_orders.booking_at', '<=', $requestParams['booking_at']['to']);
                    });
            })
            ->when(isset($requestParams['status']) && $requestParams['status'], function ($query) use ($requestParams) {
                return $query->where('store_orders.status', '=', $requestParams['status']);
            })
            ->when(isset($requestParams['agency_status']) && $requestParams['agency_status'], function ($query) use ($requestParams) {
                return $query->where('store_orders.agency_status', '=', $requestParams['agency_status']);
            });
    }

    public function getDataForListScreen(
        $with = [],
        $requestParams = [],
        $showOption = []
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->makeQueryBySearchParam($with, $requestParams);

        return $this->showOption($query, $showOption);
    }

    public function getQueryExportListScreen(
        $with = [],
        $requestParams = [],
        $showOption = []
    )
    {
        $query = $this->makeQueryBySearchParam($with, $requestParams);

        foreach ($showOption['orderBy'] ?? [] as $orderBy) {
            if (isset($orderBy['column'])) {
                $query->orderBy($orderBy['column'], $orderBy['type'] ?? 'DESC');
            }
        }

        return $query;
    }

    public function getStoreOrderPayed($storeOrderIds)
    {
        return $this->model->whereIn('id', $storeOrderIds)
            ->where(function ($q) {
                return $q->where('status', '!=', StoreOrder::STATUS_DA_GIAO)
                    ->orWhere('agency_status', '!=', StoreOrder::AGENCY_STATUS_CHUA_THANH_TOAN);
            })
            ->get();
    }

    public function getYearRangeOfUser($userId)
    {
        $orders = $this->model->where('user_id', $userId)
            ->where('status', StoreOrder::STATUS_DA_GIAO)
            ->get();

        return [
            'max' => Carbon::now()->year,
            'min' => Carbon::create($orders->min('booking_at'))->year,
        ];
    }

    public function getMonthTotalAmount($userId, $month)
    {
        $order = $this->model->where('user_id', $userId)
            ->where('booking_at', '>=', "$month-01")
            ->where('booking_at', '<=', "$month-31")
            ->get();

        return $order->sum('total_amount');
    }

    public function getTurnoverOfStore($storeId, $from, $to)
    {

    }

    public function getQueryForStatementAgency($requestParams)
    {
        return $this->model
            ->with(['items', 'agency', 'sale', 'ward', 'district', 'province', 'agencyOrder', 'store'])
            ->when(isset($requestParams['agency_id']), function ($q) use ($requestParams) {
                return $q->where('agency_id', $requestParams['agency_id']);
            })
            ->when(isset($requestParams['order_code']), function ($q) use ($requestParams) {
                return $q->where('order_code', $requestParams['order_code']);
            })
            ->when(isset($requestParams['organization_ids']) && count($requestParams['organization_ids']), function ($q) use ($requestParams) {
                return $q->whereIn('organization_id', $requestParams['organization_ids']);
            })
            ->when(isset($requestParams['booking_at']), function (Builder $query) use ($requestParams) {
                return $query->whereHas('agencyOrder', function ($q2) use ($requestParams) {
                    if ($requestParams['booking_at']['from']) {
                        $q2->where('booking_at', '>=', $requestParams['booking_at']['from']);
                    }
                    if ($requestParams['booking_at']['to']) {
                        $q2->where('booking_at', '<=', $requestParams['booking_at']['to']);
                    }
                });
            });
    }

    public function getProductInOrderList($requestParams)
    {
        return $this->model
            ->join('store_order_items', 'store_orders.id', '=', 'store_order_items.store_order_id')
            ->select('store_order_items.product_name')
            ->when(isset($requestParams['agency_id']), function ($q) use ($requestParams) {
                return $q->where('store_orders.agency_id', $requestParams['agency_id']);
            })
            ->when(isset($requestParams['organization_ids']) && count($requestParams['organization_ids']), function ($q) use ($requestParams) {
                return $q->whereIn('store_orders.organization_id', $requestParams['organization_ids']);
            })
            ->when(isset($requestParams['order_code']), function ($q) use ($requestParams) {
                return $q->where('store_orders.order_code', $requestParams['order_code']);
            })
            ->when(isset($requestParams['booking_at']), function (Builder $query) use ($requestParams) {
                return $query->whereHas('agencyOrder', function ($q2) use ($requestParams) {
                    if ($requestParams['booking_at']['from']) {
                        $q2->where('agency_orders.booking_at', '>=', $requestParams['booking_at']['from']);
                    }
                    if ($requestParams['booking_at']['to']) {
                        $q2->where('agency_orders.booking_at', '<=', $requestParams['booking_at']['to']);
                    }
                });
            })
            ->groupBy('store_order_items.product_name')
            ->orderBy('store_order_items.product_name', 'ASC')
            ->get()
            ->pluck('product_name')
            ->toArray();
    }

    public function getDataForStatementAgency($requestParams, $showOption)
    {
        $query = $this->getQueryForStatementAgency($requestParams);

        return $this->showOption($query, $showOption);
    }

    public function getLastBookingAtByStoreAndTDV($storeId = null, $tdvId = [])
    {
        return $this->model
            ->selectRaw("MAX(booking_at) as latest_booking, store_orders.*")
            ->where('store_id', $storeId)
            ->whereIn('user_id', $tdvId)
            ->groupBy('user_id')
            ->get();
    }

    public function getDeliveryOrder($from, $to, $agencyId = null)
    {
        return $this->model
            ->with('items')
            ->when(isset($agencyId), function ($q) use ($agencyId) {
                return $q->where('agency_id', $agencyId);
            })
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->whereNotNull('delivery_at')
            ->get();
    }

    public function getOrderTTKey($storeId, $productType, $status = null): array
    {
        $orders = $this->model::query()
            ->where('order_type', StoreOrder::ORDER_TYPE_DON_TTKEY)
            ->where('parent_id', 0)
            ->where('store_id', $storeId)
            ->where('ttk_product_type', $productType)
            ->where('created_at', '>=', now()->firstOfMonth()->format('Y-m-d 00:00:00'))
            ->where('created_at', '<=', now()->format('Y-m-d H:i:s'))
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->get();

        return [
            'orders'       => $orders,
            'total_amount' => $orders->sum('total_amount'),
        ];
    }

    public function countOrderChild($parentId): int
    {
        return $this->model::query()
            ->where('parent_id', $parentId)
            ->count();
    }

    public function getLastOrderOfUser($userIds, $from, $to)
    {
        return $this->model
            ->selectRaw("
                created_by,
                store_id,
                max(booking_at) as last_booking_at
            ")
            ->whereNotIn('status', [
                StoreOrder::STATUS_DA_XOA,
                StoreOrder::STATUS_DA_HUY,
                StoreOrder::STATUS_TRA_LAI
            ])
            ->where('booking_at', '>=', $from)
            ->where('booking_at', '<=', $to)
            ->groupBy('store_id', 'created_by')
            ->get();

    }
}
