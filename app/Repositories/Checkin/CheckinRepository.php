<?php

namespace App\Repositories\Checkin;

use App\Helpers\Helper;
use App\Models\Checkin;
use App\Models\Organization;
use App\Repositories\BaseRepository;
use Carbon\Carbon;

class CheckinRepository extends BaseRepository implements CheckinRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new Checkin();
    }


    public function getStoresChecked($tdvId, $from, $to)
    {
        return $this->model
            ->where('checkin_at', '>=', $from)
            ->where('checkin_at', '<=', $to)
            ->where('created_by', $tdvId)
            ->get();
    }

    public function getToDayCheckin($storeId, $tdvId)
    {
        $currentDate = Carbon::now()->format('Y-m-d');

        return $this->model
            ->where('checkin_at', '>=', "$currentDate 00:00:00")
            ->where('checkin_at', '<=', "$currentDate 23:59:59")
            ->whereNull('forget')
            ->whereNull('checkout_at')
            ->where('created_by', $tdvId)
            ->where('store_id', $storeId)
            ->first();
    }

    public function getToDayChecked($storeId, $tdvId)
    {
        $currentDate = Carbon::now()->format('Y-m-d');

        return $this->model
            ->where('checkin_at', '>=', "$currentDate 00:00:00")
            ->where('checkin_at', '<=', "$currentDate 23:59:59")
            ->whereNotNull('checkout_at')
            ->where('created_by', $tdvId)
            ->where('store_id', $storeId)
            ->first();
    }

    public function getByRequest($with = [], $requestParams = [], $showOption = [])
    {
        $query = $this->model->with($with)
            ->when(isset($requestParams['tdv_id']), function ($q) use ($requestParams) {
                return $q->where('created_by', $requestParams['tdv_id']);
            })
            ->when(isset($requestParams['tdv_name']), function ($q) use ($requestParams) {
                return $q->whereHas('user', function ($q1) use ($requestParams) {
                    return $q1->where('name', 'like', '%' . $requestParams['tdv_name'] . '%');
                });
            })
            ->when(isset($requestParams['store_name']), function ($q) use ($requestParams) {
                return $q->whereHas('store', function ($q1) use ($requestParams) {
                    return $q1->where('name', 'like', '%' . $requestParams['store_name'] . '%');
                });
            })
            ->when(isset($requestParams['store_id']), function ($q) use ($requestParams) {
                return $q->where('store_id', $requestParams['store_id']);
            })
            ->when(isset($requestParams['checkin_at_date']), function ($q) use ($requestParams) {
                return $q->where('checkin_at', '>=', $requestParams['checkin_at_date'] . ' 00:00:00')
                    ->where('checkin_at', '<=', $requestParams['checkin_at_date'] . ' 23:59:59');
            })
            ->when(isset($requestParams['checkin_at']['from']), function ($q) use ($requestParams) {
                return $q->where('checkin_at', '>=', $requestParams['checkin_at']['from'] . ' 00:00:00');
            })
            ->when(isset($requestParams['checkin_at']['to']), function ($q) use ($requestParams) {
                return $q->where('checkin_at', '<=', $requestParams['checkin_at']['to'] . ' 23:59:59');
            });

        return $this->showOption($query, $showOption);
    }

    public function getLastCheckInOfTdvWithStore($tdvId = null, $storeId = null)
    {
        return $this->model
            ->where('store_id', $storeId)
            ->where('created_by', $tdvId)
            ->where('forget', 0)
            ->max('checkin_at');
    }

    public function getCheckinInfoOfIds($userIds, $from, $to)
    {
        return $this->model
            ->selectRaw("
                created_by as user_id,
                store_id,
                max(checkin_at) as last_checkin,
                count(*) as checkin_qty
            ")
            ->where('checkin_at', '>=', "$from 00:00:00")
            ->where('checkin_at', '<=', "$to 00:00:00")
            ->whereIn('created_by', $userIds)
            ->groupBy('created_by', 'store_id')
            ->get();
    }
}
