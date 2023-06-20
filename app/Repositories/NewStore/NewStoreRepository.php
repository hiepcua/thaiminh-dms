<?php

namespace App\Repositories\NewStore;

use App\Helpers\Helper;
use App\Models\NewStore;
use App\Models\Organization;
use App\Models\Store;
use App\Models\StoreChange;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class NewStoreRepository extends BaseRepository implements NewStoreRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new NewStore();
    }

    public function getByRequest($with = [], $requestParams = [], $showOption = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $organizationOfCurrentUser = Helper::getUserOrganization();

        $query = $this->getQueryByRequest($with, $requestParams)
            ->when(isset($organizationOfCurrentUser[Organization::TYPE_KHU_VUC]), function ($q) use ($organizationOfCurrentUser) {
                return $q->whereHas('organization', function ($query2) use ($organizationOfCurrentUser) {
                    return $query2->whereIn('parent_id', $organizationOfCurrentUser[Organization::TYPE_KHU_VUC]);
                });
            });

        return $this->showOption($query, $showOption);
    }

    /**
     * @param $with
     * @param $requestParams
     * @param $showOption
     * @return mixed
     */
    public function getByRequestTDV($with = [], $requestParams = [], $showOption = [])
    {
        $query = $this->getQueryByRequest($with, $requestParams);
        return $this->showOption($query, $showOption);
    }

    public function getQueryByRequest($with = [], $requestParams = [])
    {
        return $this->model->with($with)
            ->when($requestParams['name'] ?? '', function ($query) use ($requestParams) {
                return $query->where(function ($q) use ($requestParams) {
                    return $q->where('name', 'LIKE', '%' . $requestParams['name'] . '%')
                        ->orWhere('code', $requestParams['name']);
                });
            })
            ->when($requestParams['status'] ?? '', function (Builder $query) use ($requestParams) {
                if ($requestParams['status'] == NewStore::STATUS_ALL) {
                    $status = [
                        NewStore::STATUS_ACTIVE,
                        NewStore::STATUS_INACTIVE,
                        NewStore::STATUS_NOT_APPROVED,
                    ];
                } else {
                    $status = (array)$requestParams['status'];
                }
                $query->whereIn('status', $status);
            })
            ->when($requestParams['disable'] ?? '', function (Builder $query) use ($requestParams) {
                $query->whereIn('is_disabled', $requestParams['disable']);
            })
            ->when($requestParams['created_by'] ?? '', function (Builder $query) use ($requestParams) {
                $query->where('created_by', (int)$requestParams['created_by']);
            })
            ->when($requestParams['locality_ids'] ?? '', function ($query) use ($requestParams) {
                return $query->whereIn('organization_id', $requestParams['locality_ids']);
            });
    }

    public function notApprove($newStoreId = null, $reason = null): bool
    {
        $newStore             = parent::find($newStoreId);
        $newStore->updated_by = Helper::currentUser()->id;
        $newStore->status     = NewStore::STATUS_NOT_APPROVED;
        $newStore->reason     = $reason;

        return $newStore->save();
    }

    public function checkUserCreated($userId = null, $newStoreId = null)
    {
        return $this->model
            ->where('id', $newStoreId)
            ->where('created_by', $userId)
            ->first();
    }
}
