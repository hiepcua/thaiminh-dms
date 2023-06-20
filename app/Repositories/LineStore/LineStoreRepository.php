<?php

namespace App\Repositories\LineStore;

use App\Helpers\Helper;
use App\Models\Line;
use App\Models\LineStore;
use App\Models\Organization;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class LineStoreRepository extends BaseRepository implements LineStoreRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new LineStore();
    }

    public function getByStoreIds(array $arrayId = [], $lineId = null)
    {
        return $this->model
            ->whereIn('store_id', $arrayId)
            ->when($lineId ?? '', function ($query) use ($lineId) {
                $query->where('line_id', $lineId);
            })
            ->get();
    }

    public function reopenStoreInLine($lineStoreId = null, array $attributes = [])
    {
        $currentUser = Helper::currentUser();
        return $this->model
            ->where('id', $lineStoreId)
            ->update([
                'to'           => null,
                'number_visit' => $attributes['number_visit'] ?? LineStore::DEFAULT_NUMBER_VISIT,
                'updated_by'   => $attributes['updated_by'] ?? $currentUser->id,
                'status'       => LineStore::STATUS_ACTIVE,
            ]);
    }

    // 1 tuyen, 1 nha thuoc, 1 ngay, 1 type, so lan ghe tham => chi duoc ghi nhan 1 ban ghi.
    public function getLineStoreStatusExistInDay($lineId = null, $storeId = null, $date = null, $reference_type = null, $number_visit = null)
    {
        $query = $this->model
            ->when($lineId ?? '', function ($query) use ($lineId) {
                return $query->where('line_id', $lineId);
            })
            ->when($storeId ?? '', function ($query) use ($storeId) {
                return $query->where('store_id', $storeId);
            })
            ->when($date ?? '', function ($query) use ($date) {
                return $query->whereDate('created_at', $date);
            })
            ->when($reference_type ?? '', function ($query) use ($reference_type) {
                return $query->where('reference_type', $reference_type);
            })
            ->when($number_visit ?? '', function ($query) use ($number_visit) {
                return $query->where('number_visit', $number_visit);
            });

        return $query->get();
    }

    public function processCreate(array $attributes = [])
    {
        $currentUser            = Helper::currentUser();
        $currentDate            = now()->toDateString();
        $item['line_id']        = $attributes['line_id'] ?? null;
        $item['store_id']       = $attributes['store_id'] ?? null;
        $item['from']           = $attributes['from'] ?? null;
        $item['to']             = null;
        $item['number_visit']   = $attributes['number_visit'] ?? null;
        $item['reference_type'] = $attributes['reference_type'] ?? null;
        $item['created_by']     = $currentUser->id ?? null;
        $item['updated_by']     = null;
        $item['status']         = $attributes['status'] ?? null;
        $existLineStores        = $this->getLineStoreStatusExistInDay($item['line_id'], $item['store_id'], $currentDate, $item['reference_type'], $item['number_visit']);

        if (!$existLineStores->count()) {
            parent::create($item);
        } else {
            $existIds = $existLineStores->pluck('id')->toArray() ?? [];
            $this->activeLineStoreByArrId($existIds);
        }
    }

    public function activeLineStoreByArrId(array $lineStore = [])
    {
        $this->model->whereIn('id', $lineStore)->update([
            'to'         => null,
            'updated_by' => $currentUser->id ?? null,
            'status'     => LineStore::STATUS_ACTIVE
        ]);
    }

    public function addNew(array $attributes = [])
    {
        $currentUser            = Helper::currentUser();
        $userRoleName           = $currentUser?->roles?->first()->name;
        $item                   = [];
        $item['line_id']        = $attributes['line_id'];
        $item['store_id']       = $attributes['store_id'];
        $item['created_by']     = $currentUser->id ?? null;
        $item['reference_type'] = $attributes['reference_type'] ?? null;

        if ($userRoleName == User::ROLE_TDV) {
            $item['from']         = $attributes['from'] ?? null;
            $item['number_visit'] = $attributes['number_visit'] ?? null;
            $item['status']       = LineStore::STATUS_PENDING;
        } else {
            $item['from']         = $attributes['from'] ?? now()->toDateString();
            $item['number_visit'] = $attributes['number_visit'] ?? LineStore::DEFAULT_NUMBER_VISIT;
            $item['status']       = $attributes['status'] ?? LineStore::STATUS_ACTIVE;
        }

        return $this->create($item);
    }

    public function insertNewLineStore(array $attributes = [])
    {
        $currentUser = Helper::currentUser();
        $currentDate = now()->toDateString();
        $item        = [
            'line_id'        => $attributes['line_id'] ?? null,
            'store_id'       => $attributes['store_id'] ?? null,
            'from'           => $currentDate,
            'to'             => null,
            'number_visit'   => $attributes['number_visit'] ?? LineStore::DEFAULT_NUMBER_VISIT,
            'reference_type' => $attributes['reference_type'] ?? LineStore::DEFAULT_REFERENCE_TYPE,
            'created_by'     => $currentUser->id ?? null,
            'status'         => $attributes['status'] ?? LineStore::STATUS_INACTIVE,
        ];

        return parent::create($item);
    }

    public function getList(array $requestParams = [])
    {
        $query = $this->model
            ->when($requestParams['line_id'] ?? null, function ($query) use ($requestParams) {
                $query->where('line_id', $requestParams['line_id']);
            })
            ->when($requestParams['store_id'] ?? null, function ($query) use ($requestParams) {
                $query->where('store_id', $requestParams['store_id']);
            })
            ->when($requestParams['from'] ?? null, function ($query) use ($requestParams) {
                $query->where('from', $requestParams['from']);
            })
            ->when($requestParams['to'] ?? null, function ($query) use ($requestParams) {
                $query->where('to', $requestParams['to']);
            })
            ->when($requestParams['number_visit'] ?? null, function ($query) use ($requestParams) {
                $query->where('number_visit', $requestParams['number_visit']);
            })
            ->when($requestParams['reference_type'] ?? null, function ($query) use ($requestParams) {
                $query->where('reference_type', $requestParams['reference_type']);
            })
            ->when($requestParams['created_by'] ?? null, function ($query) use ($requestParams) {
                $query->where('created_by', $requestParams['created_by']);
            })
            ->when($requestParams['updated_by'] ?? null, function ($query) use ($requestParams) {
                $query->where('updated_by', $requestParams['updated_by']);
            })
            ->when($requestParams['status'] ?? null, function ($query) use ($requestParams) {
                $query->where('status', $requestParams['status']);
            })
            ->when($requestParams['created_at'] ?? null, function ($query) use ($requestParams) {
                $query->whereDate('created_at', $requestParams['created_at']);
            });

        return $query->get();
    }

    public function getByRequest($with = [], $requestParams = [], $showOption = [])
    {
        $organizationOfCurrentUser = Helper::getUserOrganization();

        $query = $this->model
            ->with($with)
            ->when($requestParams['reference_type'] ?? '', function ($query) use ($requestParams) {
                $query->where('reference_type', $requestParams['reference_type']);
            })
            ->when($requestParams['status'] ?? '', function ($query) use ($requestParams) {
                if ($requestParams['status'] == LineStore::STATUS_ALL) {
                    $status = [
                        LineStore::STATUS_ACTIVE,
                        LineStore::STATUS_INACTIVE,
                        LineStore::STATUS_PENDING,
                        LineStore::STATUS_NOT_APPROVE,
                    ];
                } else {
                    $status = (array)$requestParams['status'];
                }

                $query->whereIn('status', $status);
            })
            ->when($requestParams['created_by'] ?? '', function ($query) use ($requestParams) {
                $query->where('created_by', $requestParams['created_by']);
            })
            ->when($requestParams['line-name'] ?? '', function ($query) use ($requestParams) {
                return $query->whereHas('line', function ($q) use ($requestParams) {
                    $q->where('name', 'LIKE', '%' . $requestParams['line-name'] . '%');
                });
            })
            ->when($requestParams['store-name'] ?? '', function ($query) use ($requestParams) {
                return $query->whereHas('store', function ($q) use ($requestParams) {
                    $q->where('name', 'LIKE', '%' . $requestParams['store-name'] . '%')
                        ->orWhere('code', $requestParams['store-name']);
                });
            })
            ->when($requestParams['locality_ids'] ?? '', function ($query) use ($requestParams) {
                return $query->whereHas('store.organization', function ($q) use ($requestParams) {
                    return $q->whereIn('id', $requestParams['locality_ids']);
                });
            })
            ->when(isset($organizationOfCurrentUser[Organization::TYPE_KHU_VUC]), function ($q) use ($organizationOfCurrentUser) {
                return $q->whereHas('organization', function ($query2) use ($organizationOfCurrentUser) {
                    return $query2->whereIn('parent_id', $organizationOfCurrentUser[Organization::TYPE_KHU_VUC]);
                });
            });

        return $this->showOption($query, $showOption);
    }
}
