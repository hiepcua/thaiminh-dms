<?php

namespace App\Repositories\AgencyOrderFile;

use App\Helpers\Helper;
use App\Models\AgencyOrderFile;
use App\Models\Organization;
use App\Repositories\BaseRepository;

class AgencyOrderFileRepository extends BaseRepository implements AgencyOrderFileRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new AgencyOrderFile();
    }

    protected function makeQueryBySearchParam($with = [], $requestParams = [])
    {
        $currentUser = Helper::currentUser();
        $organizationOfCurrentUser = Helper::getUserOrganization($currentUser);

        return $this->model
            ->with($with)
            ->join('agencies', 'agencies.id', '=', 'agency_order_files.agency_id')
            ->selectRaw("
                agency_order_files.*,
                agencies.name as agency_name
            ")
            ->when(isset($requestParams['order_code']), function ($query) use ($requestParams) {
                $code = $requestParams['order_code'];

                return $query->where('agency_order_files.order_code', 'like', '%' . $code . '%');
            })
            ->when(isset($requestParams['agency_id']), function ($query) use ($requestParams) {
                return $query->where('agency_id', $requestParams['agency_id']);
            })
            ->when(isset($requestParams['division_id']), function ($query) use ($requestParams) {
                return $query->whereHas('agency', function ($query1) use ($requestParams) {
                    return $query1->whereHas('organizations', function ($query2) use ($requestParams) {
                        return $query2->where('parent_id', $requestParams['division_id'])
                            ->where('organizations.type', '=', Organization::TYPE_DIA_BAN);
                    });
                });
            })
            ->when(isset($requestParams['locality_id']), function ($query) use ($requestParams) {
                return $query->whereHas('agency', function ($query1) use ($requestParams) {
                    return $query1->whereHas('organizations', function ($query2) use ($requestParams) {
                        return $query2->where('id', $requestParams['locality_id'])
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
                return $query->whereHas('agencyOrder', function ($query1) use ($requestParams) {
                    return $query1->where('booking_at', '>=', $requestParams['booking_at'] . ' 00:00:00')
                        ->where('booking_at', '<=', $requestParams['booking_at'] . ' 23:59:59');
                });
            });
    }

    public function getDataForListScreen($requestParams, $showOption)
    {
        $query = $this->makeQueryBySearchParam(['agencyOrder.creator'], $requestParams);

        return $this->showOption($query, $showOption);
    }
}
