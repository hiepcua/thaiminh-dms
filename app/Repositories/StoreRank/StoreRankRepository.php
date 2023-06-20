<?php

namespace App\Repositories\StoreRank;

use App\Helpers\Helper;
use App\Models\StoreRank;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StoreRankRepository extends BaseRepository implements StoreRankRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new StoreRank();
    }

    public function queryKey(array $searchParams, bool $joinItems = false)
    {
        $query = $this->model::query()
            ->when($searchParams['store_ids'], function ($query) use ($searchParams) {
                $query->whereIn('store_ranks.store_id', (array)$searchParams['store_ids']);
            })
            ->when($searchParams['locality_id'], function ($query) use ($searchParams) {
                $query->join('stores', 'stores.id', '=', 'store_ranks.store_id')
                    ->whereIn('stores.organization_id', (array)$searchParams['locality_id']);
            })
            ->where('store_ranks.store_type', $searchParams['store_type'])
            ->where('store_ranks.product_type', $searchParams['product_type'])
            ->where('store_ranks.from_date', $searchParams['from_date'])
            ->where('store_ranks.to_date', $searchParams['to_date']);

        if ($joinItems) {
            $query->join('store_rank_items', 'store_rank_items.store_rank_unique_key', '=', 'store_ranks.unique_key');
        }

        return $query;
    }

    /**
     * @param array $searchParams
     * @param bool $returnQuery
     * @return array|Builder|Collection|mixed
     */
    public function getStoreSummary(array $searchParams, bool $returnQuery = false)
    {
        $query = $this->queryKey($searchParams)
            ->with([
                'store', 'store.store_parent',
                'store.organization', 'store.organization.users',
                'store.organization.parent.users',
            ])
            ->select(['store_id', 'store_parent_id'])
            ->selectRaw('SUM(store_ranks.revenue) AS revenue')
            ->selectRaw('SUM(store_ranks.bonus) AS bonus')
            ->selectRaw('SUM(store_ranks.bonus_product_priority) AS bonus_product_priority')
//            ->whereRaw('(store_ranks.bonus + store_ranks.bonus_product_priority) > 0')
            ->groupBy('store_ranks.store_id');

        return $returnQuery ? $query : $query->get();
    }

    public function queryStoreDetail(array $searchParams)
    {
        $query = $this->queryKey($searchParams)
            ->with([
                'store', 'store.store_parent',
                'store.province', 'store.district', 'store.ward',
                'store.organization', 'store.organization.users',
                'store.organization.parent.users',
            ])
            ->select(['store_id', 'store_parent_id', 'group_id'])
            ->groupBy('store_ranks.store_id');
        $query->selectRaw("SUM(store_ranks.revenue) AS revenue_total");
        foreach ($searchParams['_group_ids'] as $gid) {
            $query->selectRaw("GROUP_CONCAT(DISTINCT IF(store_ranks.group_id = $gid, store_ranks.rank, NULL)) AS rank_$gid");
            $query->selectRaw("SUM( IF(store_ranks.group_id = $gid, store_ranks.revenue, 0) ) AS revenue_$gid");
            $query->selectRaw("SUM( IF(store_ranks.group_id = $gid, store_ranks.bonus, 0) ) AS bonus_$gid");
//            $query->selectRaw("SUM( IF(store_ranks.group_id = $gid, store_ranks.rate, 0) ) AS rate_$gid");
//            $query->selectRaw("SUM( IF(store_ranks.group_id = $gid, store_ranks.rate_product_priority, 0) ) AS rate_product_priority_$gid");
            $query->selectRaw("SUM( IF(store_ranks.group_id = $gid, store_ranks.bonus_product_priority, 0) ) AS bonus_product_priority_$gid");
        }
        foreach ($searchParams['_group_tree'] ?? [] as $gid => $subIds) {
            foreach ($subIds as $subId) {
                $query->selectRaw("SUM( IF(store_ranks.group_id = $gid AND store_ranks.sub_group_id = $subId, store_ranks.revenue, 0) ) AS revenue_{$gid}_$subId");
            }
        }

        return $query;
    }

    public function countPriority(array $storeIds, array $searchParams): array
    {
        $searchParams['name'] = '';
        $query                = $this->queryKey($searchParams, true)
            ->whereIn('store_ranks.store_parent_id', $storeIds)
            ->select('store_ranks.store_parent_id');
        foreach ($searchParams['group'] ?? [] as $groupId) {
            $query->selectRaw('COUNT( IF(store_ranks.group_id = ? AND store_rank_items.is_product_priority = 1, store_rank_items.is_product_priority, NULL) ) AS p' . $groupId, [$groupId]);
        }
        return $query->get()->keyBy('store_parent_id')->toArray();
    }

    public function getProductIdsKeyQc(array $searchParams): array
    {
        $query                     = $this->queryKey($searchParams, true);
        $query->getQuery()->orders = null;
        $query->getQuery()->groups = null;

        $query->select(['store_rank_items.group_id', 'store_rank_items.sub_group_id', 'store_rank_items.product_id'])
            ->groupBy(['store_rank_items.product_id']);

        $results     = $query->get();
        $groupValues = $groupIds = $productIds = [];
        foreach ($results as $item) {
            $groupValues[$item->group_id][$item->sub_group_id][$item->product_id] = $item->product_id;
            $groupIds[$item->group_id]                                            = $item->group_id;
            $groupIds[$item->sub_group_id]                                        = $item->sub_group_id;
            $productIds[$item->product_id]                                        = $item->product_id;
        }

        return ['group_values' => $groupValues, 'group_ids' => $groupIds, 'product_ids' => $productIds];
    }

    public function detailKeyQc(array $searchParams)
    {
        $query                     = $this->queryKey($searchParams, true);
        $query->getQuery()->groups = null;
        $query->select(
            'store_rank_items.store_id',
            'store_rank_items.group_id',
            'store_rank_items.product_id',
            'store_rank_items.is_product_priority',
            'store_rank_items.quantity',
        );

        return $query->get()
            ->groupBy('store_id')
            ->map(function ($items) {
                $quantities = $priorities = [];
                foreach ($items as $item) {
                    $quantities[$item->product_id] = $item->quantity;
                    if (!isset($priorities[$item->group_id])) {
                        $priorities[$item->group_id] = 0;
                    }
                    if ($item->is_product_priority) {
                        $priorities[$item->group_id]++;
                    }
                }

                return compact('quantities', 'priorities');
            });
    }
}
