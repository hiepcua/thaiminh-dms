<?php

namespace App\Repositories\ForgetCheckin;

use App\Models\ForgetCheckin;
use App\Repositories\BaseRepository;

class ForgetCheckinRepository extends BaseRepository implements ForgetCheckinRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new ForgetCheckin();
    }

    public function getByCreator($creatorId = null, $from = null, $to = null, $status = null)
    {
        return $this->model
            ->when($creatorId, function ($q) use ($creatorId) {
                return $q->where('created_by', $creatorId);
            })
            ->when($from, function ($q) use ($from) {
                return $q->where('created_at', '>=', $from);
            })
            ->when($to, function ($q) use ($to) {
                return $q->where('created_at', '<=', $to);
            })
            ->when($status, function ($q) use ($status) {
                if (is_array($status)) {
                    return $q->whereIn('status', $status);
                } else {
                    return $q->where('status', $status);
                }
            })
            ->get();
    }

    public function getByRequest($with = [], $requestParams = [], $showOption = [])
    {
        $query = $this->model->with($with)
            ->when(isset($requestParams['tdv_name']), function ($q) use ($requestParams) {
                return $q->whereHas('creator', function ($q1) use ($requestParams) {
                    return $q1->where('name', 'like', '%' . $requestParams['tdv_name'] . '%');
                });
            })
            ->when(isset($requestParams['user_id']), function ($q) use ($requestParams) {
                return $q->whereHas('creator', function ($q1) use ($requestParams) {
                    return $q1->where('id', $requestParams['user_id']);
                });
            })
            ->when(isset($requestParams['store_name']), function ($q) use ($requestParams) {
                return $q->whereHas('checkin', function ($q1) use ($requestParams) {
                    return $q1->whereHas('store', function ($q2) use ($requestParams) {
                        return $q2->where('name', 'like', '%' . $requestParams['store_name'] . '%');
                    });
                });
            })
            ->when(isset($requestParams['created_at']['from']), function ($q) use ($requestParams) {
                return $q->where('created_at', '>=', $requestParams['created_at']['from'] . ' 00:00:00');
            })
            ->when(isset($requestParams['created_at']['to']), function ($q) use ($requestParams) {
                return $q->where('created_at', '<=', $requestParams['created_at']['to'] . ' 23:59:59');
            })
            ->when(isset($requestParams['status']), function ($q) use ($requestParams) {
                return $q->where('status', $requestParams['status']);
            });

        return $this->showOption($query, $showOption);
    }
}
