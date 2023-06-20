<?php

namespace App\Repositories\ReportAgencyInventory;

use App\Models\ReportAgencyInventory;
use App\Repositories\BaseRepository;

class ReportAgencyInventoryRepository extends BaseRepository implements ReportAgencyInventoryRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new ReportAgencyInventory();
    }

    public function getAgencyInventoryMonth($year, $month, $agencyId = null)
    {
        return $this->model
            ->where('year', $year)
            ->where('month', $month)
            ->when(isset($agencyId), function ($q) use ($agencyId) {
                return $q->where('agency_id', $agencyId);
            })
            ->whereNotNull('product_id')
            ->whereNotNull('agency_id')
            ->get();
    }

    protected function makeQueryBySearchParam($requestParams)
    {
        return $this->model;
    }

    public function getDataForListScreen(
        $requestParams = [],
        $showOption = []
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->makeQueryBySearchParam($requestParams);

        return $this->showOption($query, $showOption);
    }

    public function getByAgencyIdsMonth($agencyIds, $with, $requestData)
    {
        return $this->model
            ->with($with ?? [])
            ->whereIn('agency_id', $agencyIds)
            ->when(isset($requestData['month']), function ($q) use ($requestData) {
                return $q->where('month', $requestData['month']);
            })
            ->when(isset($requestData['year']), function ($q) use ($requestData) {
                return $q->where('year', $requestData['year']);
            })
            ->when(isset($requestData['status']), function ($q) use ($requestData) {
                if ($requestData['status'] == ReportAgencyInventory::STATUS_INVENTORY_ENOUGH) {
                    return $q->where('inventory_num', '>=', 0);
                }
                if ($requestData['status'] == ReportAgencyInventory::STATUS_INVENTORY_NOT_ENOUGH) {
                    return $q->where('inventory_num', '<', 0);
                }
            })
            ->when((isset($requestData['product_type']) || isset($requestData['codeOrNameProduct']))
                && isset($requestData['product_ids']), function ($q) use ($requestData) {
                return $q->whereIn('product_id', $requestData['product_ids']);
            })
            ->whereNotNull('agency_id')
            ->whereNotNull('product_id')
            ->get();
    }

    public function getInventory($agencyId, $productId, $month, $year)
    {
        return $this->model
            ->where('agency_id', $agencyId)
            ->where('product_id', $productId)
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', ReportAgencyInventory::STATUS_NOT_KC)
            ->first();
    }

    public function getQueryExportListScreen($with, $requestParams, $showOption)
    {
        return $this->model
            ->join('agencies', 'agencies.id', '=', 'report_agency_inventory.agency_id')
            ->join('products', 'products.id', '=', 'report_agency_inventory.product_id')
            ->with($with)
            ->where(function ($q) {
                return $q->where('start_num', '!=', 0)
                    ->orWhere('import_num', '!=', 0)
                    ->orWhere('export_num', '!=', 0)
                    ->orWhere('inventory_num', '!=', 0);
            })
            ->whereNotNull('product_id')
            ->whereNotNull('agency_id')
            ->when(isset($requestParams['year']), function ($q) use ($requestParams) {
                return $q->where('year', $requestParams['year']);
            })
            ->when(isset($requestParams['month']), function ($q) use ($requestParams) {
                return $q->where('month', $requestParams['month']);
            })
            ->when(isset($requestParams['agency_ids']), function ($q) use ($requestParams) {
                return $q->whereIn('agency_id', $requestParams['agency_ids']);
            })
            ->when(isset($requestParams['product_ids']), function ($q) use ($requestParams) {
                return $q->whereIn('product_id', $requestParams['product_ids']);
            })
            ->when(isset($requestParams['status']), function ($q) use ($requestParams) {
                if ($requestParams['status'] == ReportAgencyInventory::STATUS_INVENTORY_ENOUGH) {
                    return $q->where('inventory_num', '>=', 0);
                }

                if ($requestParams['status'] == ReportAgencyInventory::STATUS_INVENTORY_NOT_ENOUGH) {
                    return $q->where('inventory_num', '<', 0);
                }
            })
            ->orderBy('agencies.name', 'asc')
            ->orderBy('products.name', 'asc');
    }

    public function getLatestInventoryConfirmed($agencyId)
    {
        return $this->model->where('id', $agencyId)
            ->where('status', ReportAgencyInventory::STATUS_KC)
            ->orderBy('year', 'DESC')
            ->orderBy('month', 'DESC')
            ->first();
    }
}
