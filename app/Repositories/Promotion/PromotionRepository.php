<?php

namespace App\Repositories\Promotion;

use App\Helpers\Helper;
use App\Models\Organization;
use App\Models\OrganizationPromotion;
use App\Models\Promotion;
use App\Repositories\BaseRepository;

class PromotionRepository extends BaseRepository implements PromotionRepositoryInterface
{
    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new Promotion();
    }

    protected function getOrganizationSearch($id, &$result = [])
    {
        $organization = Organization::find($id);
        if ($organization) {
            $result[] = $id;

            if ($organization->parent_id != 0) {
                self::getOrganizationSearch($organization->parent_id, $result);
            } else {
                return $result;
            }
        }

        return $result;
    }

    public function getByRequest($paginate, $with = [], $requestParams = [])
    {
        $currentUser               = Helper::currentUser();
        $organizationOfCurrentUser = Helper::getUserOrganization($currentUser);

        $idOrganizationSearch = [];
        if (isset($requestParams['division_id'])) {
            foreach ($requestParams['division_id'] as $divisionId) {
                $idOrganizationSearch = array_unique(array_merge(self::getOrganizationSearch($divisionId), $idOrganizationSearch));
            }
        }

        return $this->model
            ->with($with)
            ->when(isset($requestParams['name']), function ($query) use ($requestParams) {
                return $query->where('name', 'like', "%" . $requestParams['name'] . "%");
            })
            ->when(isset($requestParams['type']), function ($query) use ($requestParams) {
                return $query->whereHas('promotionConditions', function ($q) use ($requestParams) {
                    return $q->where('type', $requestParams['type']);
                });
            })
            ->when(isset($requestParams['status']), function ($query) use ($requestParams) {
                return $query->where('status', $requestParams['status']);
            })
            ->whereHas('organizations', function ($q1) {
                return $q1->active();
            })
            ->when(isset($idOrganizationSearch) && count($idOrganizationSearch), function ($query) use ($requestParams, $idOrganizationSearch) {
                return $query->whereHas('organizationPromotions', function ($q1) use ($requestParams, $idOrganizationSearch) {
                    return $q1->whereIn('organization_id', $idOrganizationSearch)
                        ->where('type', OrganizationPromotion::TYPE_INCLUDE);
                });
            })
            ->when(isset($requestParams['division_id']), function ($query) use ($requestParams) {
                return $query->whereDoesntHave('organizationPromotions', function ($q1) use ($requestParams) {
                    return $q1->whereIn('organization_id', $requestParams['division_id'])
                        ->where('type', OrganizationPromotion::TYPE_EXCLUDE);
                });
            })
            ->when($currentUser->can('loc_du_lieu_cay_so_do') && count($organizationOfCurrentUser),
                function ($query) use ($organizationOfCurrentUser) {
                    return $query->whereHas('organizations', function ($query1) use ($organizationOfCurrentUser) {
                        return $query1->whereIn('organizations.id', $organizationOfCurrentUser[Organization::TYPE_KHU_VUC])
                            ->orWhereIn('organizations.id', $organizationOfCurrentUser[Organization::TYPE_MIEN])
                            ->orWhereIn('organizations.id', $organizationOfCurrentUser[Organization::TYPE_CONG_TY])
                            ->orWhereIn('organizations.id', $organizationOfCurrentUser[Organization::TYPE_TONG_CONG_TY]);
                    });
                })
            ->when(isset($requestParams['date']), function ($query) use ($requestParams) {
                return $query->where('started_at', '<=', $requestParams['date'] . ' 00:00:00')
                    ->where(function ($q1) use ($requestParams) {
                        return $q1->whereNull('ended_at')
                            ->orWhere('ended_at', '>=', $requestParams['date'] . ' 23:59:59');
                    });
            })
            ->orderBy('updated_at', 'DESC')
            ->paginate($paginate);
    }
}
