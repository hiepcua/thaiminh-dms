<?php

namespace App\Repositories\StoreChange;

use App\Helpers\Helper;
use App\Models\NewStore;
use App\Models\Organization;
use App\Models\StoreChange;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StoreChangeRepository extends BaseRepository implements StoreChangeRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new StoreChange();
    }

    /**
     * @param $with
     * @param $requestParams
     * @param $showOption
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByRequest($with = [], $requestParams = [], $showOption = [])
    {
        $organizationOfCurrentUser = Helper::getUserOrganization();
        $query                     = $this->getQueryByRequest($with, $requestParams)
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
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
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
                if ($requestParams['status'] == StoreChange::ALL_STATUS) {
                    $status = [
                        StoreChange::STATUS_ACTIVE,
                        StoreChange::STATUS_INACTIVE,
                        StoreChange::STATUS_NOT_APPROVE,
                    ];
                } else {
                    $status = (array)$requestParams['status'];
                }
                $query->whereIn('status', $status);
            })
            ->when($requestParams['created_by'] ?? '', function (Builder $query) use ($requestParams) {
                $query->where('created_by', (int)$requestParams['created_by']);
            })
            ->when($requestParams['locality_ids'] ?? '', function ($query) use ($requestParams) {
                return $query->whereIn('organization_id', $requestParams['locality_ids']);
            });
    }

    public function create(array $attributes = [])
    {
        $storeId     = $attributes['store_id'] ?? '';
        $storeChange = $this->model
            ->where('store_id', $storeId)
            ->where('status', StoreChange::STATUS_INACTIVE)
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($storeChange) {
            return $storeChange->update($attributes);
        }

        return parent::create($attributes);
    }

    public function updateByArrId(array $arrId = [], array $attributes = [])
    {
        return $this->model->whereIn('id', $arrId)->update($attributes);
    }

    public function notApprove($storeChangeId = null, $reason = null): bool
    {
        $storeChange             = parent::find($storeChangeId);
        $storeChange->status     = StoreChange::STATUS_NOT_APPROVE;
        $storeChange->updated_by = Helper::currentUser()->id;
        $storeChange->reason     = $reason;

        return $storeChange->save();
    }

    public function setNotApproveByStore($storeId = null)
    {
        return $this->model
            ->where('store_id', $storeId)
            ->where('status', StoreChange::STATUS_INACTIVE)
            ->update([
                'status'     => StoreChange::STATUS_NOT_APPROVE,
                'updated_by' => Helper::currentUser()->id,
                'reason'     => StoreChange::REASON_INACTIVE_STORE_TEXT,
            ]);
    }

    public function checkUserCreated($userId = null, $storeChangeId = null)
    {
        return $this->model
            ->where('id', $storeChangeId)
            ->where('created_by', $userId)
            ->first();
    }
}
