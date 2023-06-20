<?php

namespace App\Repositories\ReportRevenuePharmacy;

use App\Models\ReportRevenueStore;
use App\Repositories\BaseRepository;
use DB;

class ReportRevenuePharmacyRepository extends BaseRepository implements ReportRevenuePharmacyRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new ReportRevenueStore();
    }


    public function getByRequest(
        $with = [],
        $requestParams = [],
        $showOption = []
    )
    {
        $query = $this->getQuery(
            $with,
            $requestParams,
            $showOption);
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
        $query = $this->model
            ->leftJoin('stores', function ($join) {
                $join->on('stores.id', '=', 'report_revenue_store.store_id');
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
                SUM(total_order) as sum_total_order,
                SUM(total_sub_amount) as sum_total_sub_amount,
                SUM(total_discount) as sum_total_discount,
                SUM(total_amount) as sum_total_amount,
                report_revenue_store.*,
                stores.name as agency_name,
                stores.code as pharmacy_code,
                districts.district_name as pharmacy_district,
                provinces.province_name as pharmacy_province,
                organizations.name as pharmacy_organization,
                organizations.type as pharmacy_type
            ")
            ->with($with)
            ->when($requestParams['region'] ?? '', function ($query) use ($requestParams) {
                return $query->where('provinces.region', 'LIKE', '%' . $requestParams['region'] . '%');
            })
            ->when($requestParams['pharmacy_code'] ?? '', function ($query) use ($requestParams) {
                return $query->where('stores.code', 'LIKE', '%' . $requestParams['pharmacy_code'] . '%');
            })
            ->when($requestParams['province_id'] ?? '', function ($query) use ($requestParams) {
                return $query->where('stores.province_id', '=', $requestParams['province_id']);
            })
            ->when($requestParams['division_id'] ?? '', function ($query) use ($requestParams) {
                return $query->where('stores.organization_id', '=', $requestParams['division_id']);
            })
            ->when($requestParams['range_date'] ?? '', function ($query) use ($requestParams) {
                return $query->where('report_revenue_store.day', '>=', $requestParams['range_date']['from'])
                    ->where('report_revenue_store.day', '<=', $requestParams['range_date']['to']);
            });
        return $query->groupBy('store_id');
    }


    public function getByIdPharmacy($id, $range_date,  $showOption)
    {
        $query = DB::table('store_order_items')
            ->selectRaw("
                store_order_items.*,
                SUM(store_order_items.product_qty) as all_qty_product
            ")
            ->leftJoin('store_orders', 'store_orders.id', '=', 'store_order_items.store_order_id')
            ->where('store_orders.store_id', $id)
            ->where('store_order_items.product_type', '<>', 'discount')
            ->when($range_date ?? '', function ($query) use ($range_date) {
                return $query->where('store_orders.booking_at', '>=', $range_date['from'])
                    ->where('store_orders.booking_at', '<=', $range_date['to']);
            })
            ->groupBy('store_order_items.product_id');
//        return $this->showOption($query, $showOption);
        return $query->get();
    }
}
