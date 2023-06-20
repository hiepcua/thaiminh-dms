<?php

namespace App\Repositories\KeyRewardOrder;

use App\Models\KeyRewardOrder;
use App\Models\ReportRevenueStore;
use App\Models\StoreOrder;
use App\Repositories\BaseRepository;

class KeyRewardOrderRepository extends BaseRepository implements KeyRewardOrderRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new StoreOrder();
    }

    public function getByRequest(
        $with = [],
        $requestParams = [],
        $showOption = []
    )
    {
        $query = $this->getQuery(
            $with,
            $requestParams);
        return $this->showOption($query, $showOption);
    }

    public function getDataExport(
        $with = [],
        $requestParams = [],
        $showOption = []
    )
    {

        $query = $this->getQuery($with, $requestParams);
        foreach ($showOption['orderBy'] ?? [] as $orderBy) {
            if (isset($orderBy['column'])) {
                $query->orderBy($orderBy['column'], $orderBy['type'] ?? 'DESC');
            }
        }
        return $query;
    }


    public function getQuery(
        $with = [],
        $requestParams = [],
        $showOption = []
    )
    {
        $range_date = $requestParams['range_date'];
        $query      = $this->model
            ->leftJoin('stores', function ($join) {
                $join->on('stores.id', '=', 'store_orders.store_id');
            })
            ->leftJoin('provinces', function ($join) {
                $join->on('provinces.id', '=', 'stores.province_id');
            })
            ->leftJoin('districts', function ($join) {
                $join->on('districts.id', '=', 'stores.district_id');
            })
            ->leftJoin('organizations', function ($join) {
                $join->on('organizations.id', '=', 'stores.organization_id');
            })
            ->selectRaw("
                store_orders.*,
                stores.name as pharmacy_name,
                stores.address as pharmacy_address,
                stores.code as pharmacy_code
            ")
            ->with($with)
            ->where('store_orders.order_type', 2)
            ->where('store_orders.parent_id', 0)
            ->when($requestParams['pharmacy'] ?? '', function ($query) use ($requestParams) {
                return
                    $query->where(function ($query) use ($requestParams) {

                        $query->where('stores.code', 'LIKE', '%' . $requestParams['pharmacy'] . '%')
                            ->orwhere('stores.name', 'LIKE', '%' . $requestParams['pharmacy'] . '%');
                    });
            })
            ->when($requestParams['status'] ?? '', function ($query) use ($requestParams) {
                return $query->where('store_orders.status', '=', $requestParams['status']);
            })
            ->when($requestParams['order_logistic'] ?? '', function ($query) use ($requestParams) {
                return $query->where('store_orders.order_logistic', '=', $requestParams['order_logistic']);
            })
            ->when($requestParams['province_id'] ?? '', function ($query) use ($requestParams) {
                return $query->where('stores.province_id', '=', $requestParams['province_id']);
            })
            ->when($requestParams['province_id'] ?? '', function ($query) use ($requestParams) {
                return $query->where('stores.province_id', '=', $requestParams['province_id']);
            })
            ->when($requestParams['division_id'] ?? '', function ($query) use ($requestParams) {
                return $query->where('stores.organization_id', '=', $requestParams['division_id']);
            })
            ->when($range_date ?? '', function ($query) use ($range_date) {
                return $query->where('store_orders.booking_at', '>=', $range_date['from'])
                    ->where('store_orders.booking_at', '<=', $range_date['to']);
            });
        return $query;
    }

    public function getInfoPharmacy($id)
    {
        $data = StoreOrder::query()
            ->find($id);
        return $data;
    }

    public function getListByIdOrder($id)
    {
        $query = StoreOrder::query()
            ->where('parent_id', $id);
//        ->where('id', $id);
        return $query->get();
    }

    public function countOrder($id)
    {
        return StoreOrder::query()
            ->select('id')
            ->where('order_code', $id)
            ->count();
    }

    public function generateOrderCode($id): string
    {
        $code = 'K' . date('ym');
        $code .= mt_rand(100000, 999999);
        $code .= '_' . $this->countOrder($id);
        return $code;

    }

    public function update_order($ids, $data_update)
    {
        foreach ($ids as $id) {
            $data_update['order_code'] = $this->generateOrderCode($id);
            StoreOrder::query()
                ->where('id', $id)
                ->where('status', StoreOrder::STATUS_CHUA_GIAO)
                ->update($data_update);
        }
    }

}
