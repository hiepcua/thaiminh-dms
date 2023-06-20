<?php

namespace App\Repositories\AgencyOrder;

use App\Helpers\Helper;
use App\Models\AgencyOrder;
use App\Models\Organization;
use App\Repositories\BaseRepository;

class AgencyOrderRepository extends BaseRepository implements AgencyOrderRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new AgencyOrder();
    }

    protected function makeQueryBySearchParam($with = [], $requestParams = [])
    {
        $currentUser = Helper::currentUser();
        $organizationOfCurrentUser = Helper::getUserOrganization($currentUser);
        $divisionId = $requestParams['division_id'] ?? '';
        $localities = Organization::where('parent_id', $divisionId)->pluck('id')->toArray();

        return $this->model
            ->with($with)
            ->join('agencies', 'agencies.id', '=', 'agency_orders.agency_id')
            ->leftJoin('provinces', 'provinces.id', '=', 'agency_orders.agency_province_id')
            ->selectRaw("
                agency_orders.*,
                agencies.name as agency_name,
                provinces.province_name_with_type
            ")
            ->when(isset($requestParams['codeOrName']), function ($query1) use ($requestParams) {
                return $query1->where('agencies.code', $requestParams['codeOrName'])
                    ->orWhere('agencies.name', 'like', '%' . $requestParams['codeOrName'] . '%');
            })
            ->when(isset($requestParams['division_id']), function ($query) use ($localities) {
                return $query->whereHas('agency', function ($query1) use ($localities) {
                    return $query1->whereHas('organizations', function ($query2) use ($localities) {
                        return $query2->whereIn('id', $localities)
                            ->where('organizations.type', '=', Organization::TYPE_DIA_BAN);
                    });
                });
            })
            ->when($currentUser->can('loc_du_lieu_cay_so_do') && count($organizationOfCurrentUser),
                function ($query) use ($organizationOfCurrentUser) {
                    return $query->whereHas('agency', function ($query1) use ($organizationOfCurrentUser) {
                        return $query1->whereHas('organizations', function ($query2) use ($organizationOfCurrentUser) {
                            return $query2->whereIn('organizations.id', $organizationOfCurrentUser[Organization::TYPE_DIA_BAN]);
                        });
                    });
                })
            ->when(isset($requestParams['booking_at']), function ($query) use ($requestParams) {
                return $query->when(isset($requestParams['booking_at']['from']), function ($query1) use ($requestParams) {
                    return $query1->where('agency_orders.booking_at', '>=', $requestParams['booking_at']['from'] . ' 00:00:00');
                })
                    ->when(isset($requestParams['booking_at']['to']), function ($query1) use ($requestParams) {
                        return $query1->where('agency_orders.booking_at', '<=', $requestParams['booking_at']['to'] . ' 23:59:59');
                    });
            })
            ->when(isset($requestParams['status']) && $requestParams['status'], function ($query) use ($requestParams) {
                return $query->where('agency_orders.status', '=', $requestParams['status']);
            })
            ->when(isset($requestParams['type']) && $requestParams['type'], function ($query) use ($requestParams) {
                return $query->where('agency_orders.type', '=', $requestParams['type']);
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
    ){
        $query = $this->makeQueryBySearchParam($with, $requestParams);

        foreach ($showOption['orderBy'] ?? [] as $orderBy) {
            if (isset($orderBy['column'])) {
                $query->orderBy($orderBy['column'], $orderBy['type'] ?? 'DESC');
            }
        }

        return $query;
    }

    public function getOrderCreatedByTDV($ids)
    {
        return $this->model->where('type', AgencyOrder::TYPE_TDV_ORDER)
            ->whereIn('id', $ids)
            ->get();
    }

    public function getOrderRemoved($ids = null)
    {
        return $this->model->where('status', AgencyOrder::STATUS_HUY_DON)
            ->when(isset($ids), function ($q) use ($ids) {
                return $q->whereIn('id', $ids);
            })
            ->get();
    }

    public function getByAgencyOnMonth($agencyId, $month)
    {
        return $this->model->where('agency_id', $agencyId)
            ->where('booking_at', '>=', $month . '-01 00:00:00')
            ->where('booking_at', '<=', $month . '-31 00:00:00')
            ->where('type', AgencyOrder::TYPE_TDV_ORDER)
            ->get();
    }

    public function getTDVOrder($from, $to, $agencyId = null)
    {
        return $this->model
            ->with('agencyOrderItems')
            ->whereNotNull('booking_at')
            ->when(isset($agencyId), function ($q) use ($agencyId) {
                return $q->where('agency_id', $agencyId);
            })
            ->where('booking_at', '>=', $from)
            ->where('booking_at', '<=', $to)
            ->where('type', AgencyOrder::TYPE_AGENCY_ORDER)
            ->get();
    }
}
