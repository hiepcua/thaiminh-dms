<?php

namespace App\Repositories\Product;

use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductGroupPriority;
use App\Models\User;
use App\Models\ProductLog;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use DB;
use function Clue\StreamFilter\fun;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new Product();
    }

    public function lists(array $with = [], array $args = [])
    {
        return $this->model->with($with)->where('status', Product::STATUS_ACTIVE)->get();
    }

    public function paginate(int $limit, array $with = [], array $args = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $status = '';
        if (isset($args['status'])) {
            if ($args['status'] == 'active') $status = 1;
            if ($args['status'] == 'deactive') $status = 0;
        } else {
            $status = '';
        }

        return $this->model
            ->with($with)
            ->when($args['name'] ?? '', function ($query) use ($args) {
                $query->where('name', 'LIKE', '%' . $args['name'] . '%');
            })
            ->when($args['company_id'] ?? '', function ($query) use ($args) {
                $query->where('company_id', '=', $args['company_id']);
            })
            ->when($status !== "", function ($query) use ($status) {
                $query->where('status', '=', $status);
            })
            ->paginate($limit);
    }

    public function formOptions($model = null): array
    {
        $options                  = parent::formOptions($model); // TODO: Change the autogenerated stub
        $options['root_products'] = Product::query()->where('parent_id', 0)->get();
        $options['all_users']     = User::query()->where('status', 1)->get(['id', 'email', 'name']);

        return $options;
    }

    public function create(array $attributes = [])
    {
        $product = parent::create($attributes);
        if ($product) {
            if (!$attributes['key_id']) {
                parent::update($product->id, array('key_id' => $product->id));
            }

            $attributes['users'] = $attributes['users'] ?? [];
            $product->bm_users()->sync($attributes['users']);

            ProductLog::create([
                'product_id' => $product->id,
                'status'     => $product->status,
                'created_by' => $product->created_by,
            ]);
        }
    }

    public function update(int $id, array $attributes = [])
    {
        $product = parent::update($id, $attributes);
        if ($product) {
            $attributes['users'] = $attributes['users'] ?? [];
            $product->bm_users()->sync($attributes['users']);

            ProductLog::create([
                'product_id' => $product->id,
                'status'     => $product->status,
                'created_by' => $product->created_by,
                'updated_by' => $product->updated_by,
            ]);
        }
    }

    public function getActiveProduct($company = null)
    {
        return $this->model::when($company, function ($q) use ($company) {
            return $q->where('company_id', $company);
        })
            ->active()
            ->orderBy('products.name', 'asc')
            ->get();
    }

    public function getByRequest(
        $with = [],
        $requestParams = [],
        $showOption = []
    )
    {
        $query = $this->model
            ->with($with)
            ->when(isset($requestParams['status']) && $requestParams['status'] != Product::ALL_STATUS, function ($query) use ($requestParams) {
                return $query->where('status', $requestParams['status']);
            })
            ->when($requestParams['nameOrCode'] ?? '', function ($query) use ($requestParams) {
                return $query->where(function ($q) use ($requestParams) {
                    return $q->where('name', 'LIKE', '%' . $requestParams['nameOrCode'] . '%')
                        ->orWhere('code', 'LIKE', '%' . $requestParams['nameOrCode'] . '%');
                });
            })
            ->when($requestParams['company_id'] ?? '', function ($query) use ($requestParams) {
                $query->where('company_id', '=', $requestParams['company_id']);
            });

        return $this->showOption($query, $showOption);
    }

    public function getByArrId($arrId, $with = [], $statusActive = true)
    {
        $query = $this->model->with($with)->whereIn('id', $arrId);
        if ($statusActive) {
            $query->active();
        }

        return $query->get();
    }

    public function getForSearchInventory($productType, $codeOrName)
    {
        return $this->model
            ->when(isset($codeOrName), function ($q) use ($productType, $codeOrName) {
                return $q->where(function ($q1) use ($codeOrName) {
                    return $q1->where('name', 'like', "%$codeOrName%")
                        ->orWhere('code', $codeOrName);
                });
            })
            ->whereHas('productGroupPriorities', function ($q) use ($productType) {
                return $q->when(isset($productType), function ($q1) use ($productType) {
                    $currentDate = Carbon::now()->format('Y-m-d');
                    return $q1->where('product_type', $productType)
                        ->where('status', ProductGroupPriority::STATUS_ACTIVE)
                        ->where(function ($q) use ($currentDate) {
                            return $q->where(function ($q1) use ($currentDate) {
                                $q1->where('period_from', '<=', $currentDate)
                                    ->where('period_to', '>=', $currentDate);
                            })
                                ->orWhere(function ($q2) use ($currentDate) {
                                    $q2->where('period_from', '<=', $currentDate)
                                        ->whereNull('period_to');
                                });
                        });
                });
            })
            ->active()
            ->get();
    }
}
