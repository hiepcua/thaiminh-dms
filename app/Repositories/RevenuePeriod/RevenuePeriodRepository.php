<?php

namespace App\Repositories\RevenuePeriod;

use App\Models\Rank;
use App\Models\RevenuePeriod;
use App\Models\RevenuePeriodItem;
use App\Models\RevenueProductCondition;
use App\Repositories\BaseRepository;

class RevenuePeriodRepository extends BaseRepository implements RevenuePeriodRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new RevenuePeriod();
    }

    public function paginate(int $limit, array $with = [], array $args = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model->with($with)
            ->when($args['search']['rank'] ?? '', function ($query) use ($args) {
                $query->where('rank_id', $args['search']['rank']);
            })
            ->when($args['search']['product_type'] ?? '', function ($query) use ($args) {
                $query->where('product_type', $args['search']['product_type']);
            })
            ->when($args['search']['period'] ?? '', function ($query) use ($args) {
                $query->where('period_from', '=', $args['search']['period']);
//                    ->where('period_to', '>=', $args['search']['period']);
            })
            ->paginate($limit);
    }

    public function getData(array $with = [], array $args = [])
    {
        return $this->model->with($with)
            ->when($args['search']['rank'] ?? '', function ($query) use ($args) {
                $query->where('rank_id', $args['search']['rank']);
            })
            ->when($args['search']['product_type'] ?? '', function ($query) use ($args) {
                $query->where('product_type', $args['search']['product_type']);
            })
            ->when($args['search']['store_type'] ?? '', function ($query) use ($args) {
                $query->where('store_type', $args['search']['store_type']);
            })
            ->when($args['search']['region_apply'] ?? '', function ($query) use ($args) {
                $query->where('region_apply', $args['search']['region_apply']);
            })
            ->when($args['search']['period'] ?? '', function ($query) use ($args) {
                $query->where('period_from', '=', $args['search']['period']);
//                    ->where('period_to', '>=', $args['search']['period']);
            })
            ->orderBy('period_to','desc')
            ->get();
    }

    public function deleteChildren(array $ids)
    {
        RevenueProductCondition::query()->whereIn('revenue_period_item_id', $ids)->delete();
        RevenuePeriodItem::query()->whereIn('id', $ids)->delete();
    }

    public function getByRank($rankId, $date = null)
    {
        return $this->model->where('rank_id', $rankId)
            ->active($date)
            ->get();
    }

    public function getOldRevenue($requestData)
    {
        return $this->model
            ->where('status', RevenuePeriod::STATUS_ACTIVE)
            ->when(isset($requestData['rank_id']), function ($q) use ($requestData) {
                return $q->where('rank_id', $requestData['rank_id']);
            })
            ->when(isset($requestData['product_type']), function ($q) use ($requestData) {
                return $q->where('product_type', $requestData['product_type']);
            })
            ->when(isset($requestData['store_type']), function ($q) use ($requestData) {
                return $q->where('store_type', $requestData['store_type']);
            })
            ->when(isset($requestData['region_apply']), function ($q) use ($requestData) {
                return $q->where(function ($q2) use ($requestData) {
                    $q2->where('region_apply', $requestData['region_apply'])
                        ->orWhere('region_apply', RevenuePeriod::REGION_APPLY_ALL);
                });
            })
            ->where(function ($q) use ($requestData) {
                if (isset($requestData['period_to'])) {
                    return $q->where(function ($q2) use ($requestData) {
                        $q2->where(function ($q3) use ($requestData) {
                            $q3->where('period_from', '<=', $requestData['period_from'])
                                ->where('period_to', '>=', $requestData['period_from']);
                        })->orWhere(function ($q3) use ($requestData) {
                            $q3->where('period_from', '<=', $requestData['period_to'])
                                ->where('period_to', '>=', $requestData['period_to']);
                        })->orWhere(function ($q3) use ($requestData) {
                            $q3->where('period_from', '>=', $requestData['period_from'])
                                ->where('period_to', '<=', $requestData['period_to']);
                        });
                    });
                } else {
                    return $q->where(function ($q2) use ($requestData) {
                        $q2->where(function ($q3) use ($requestData) {
                            $q3->where('period_from', '<=', $requestData['period_from'])
                                ->where('period_to', '>=', $requestData['period_from']);
                        });
                    })
                        ->orWhere(function ($q2) use ($requestData) {
                            $q2->where('period_from', '>=', $requestData['period_from']);
                        });
                }
            })
            ->first();
    }

}
