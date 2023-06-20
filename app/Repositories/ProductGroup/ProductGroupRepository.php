<?php

namespace App\Repositories\ProductGroup;

use App\Models\ProductGroup;
use App\Repositories\BaseRepository;

class ProductGroupRepository extends BaseRepository implements ProductGroupRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new ProductGroup();
    }

    public function paginate(int $limit, array $with = [], array $args = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model
            ->with($with)
            ->when($args['name'] ?? '', function ($query) use ($args) {
                $query->where('name', 'LIKE', '%' . $args['name'] . '%');
            })
            ->orderByRaw('IF(parent_id, CONCAT(parent_id, LPAD(id, 3, 0)), CONCAT(id, LPAD(id, 3, 0)))')
            ->paginate($limit);
    }

    public function getByRequest(
        $with = [],
        $requestParams = [],
        $showOption = []
    )
    {
        $query = $this->model
            ->with($with)
            ->when($requestParams['name'] ?? '', function ($query) use ($requestParams) {
                return $query->where('name', 'LIKE', '%' . $requestParams['name'] . '%');
            })
            ->orderByRaw('IF(parent_id, CONCAT(parent_id, LPAD(id, 3, 0)), CONCAT(id, LPAD(id, 3, 0)))');

        return $this->showOption($query, $showOption);
    }

    public function getGroupByType($productType = null): \Illuminate\Database\Eloquent\Collection|array
    {
        return $this->model::query()
//            ->where('parent_id', 0)
            ->when($productType, function ($query) use ($productType) {
                $query->where('product_type', $productType);
            })
            ->ofStatus(ProductGroup::STATUS_ACTIVE)
            ->get();
    }

    public function getGroupByArrType($productType = []): \Illuminate\Database\Eloquent\Collection|array
    {
        return $this->model::query()
            ->where('parent_id', 0)
            ->when($productType, function ($query) use ($productType) {
                $query->whereIn('product_type', $productType);
            })
            ->ofStatus(ProductGroup::STATUS_ACTIVE)
            ->get();
    }

    public function getSubGroupActive()
    {
        return $this->model
            ->with('parent')
            ->where('status', ProductGroup::STATUS_ACTIVE)
            ->where('parent_id', '!=', 0)
            ->get();
    }

    public function getRootGroup()
    {
        return $this->model->query()->where('parent_id', 0)->get();
    }
}

