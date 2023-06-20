<?php

namespace App\Repositories\StoreRank;

use App\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface StoreRankRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param array $searchParams
     * @param bool $joinItems
     * @return Builder
     */
    public function queryKey(array $searchParams, bool $joinItems = false);

    /**
     * @param array $searchParams
     * @param bool $returnQuery
     * @return array|Builder|Collection|mixed
     */
    public function getStoreSummary(array $searchParams, bool $returnQuery = false);

    /**
     * @param array $searchParams
     * @return Builder
     */
    public function queryStoreDetail(array $searchParams);

    public function countPriority(array $storeIds, array $searchParams);

    public function getProductIdsKeyQc(array $searchParams): array;

    public function detailKeyQc(array $searchParams);
}
