<?php

namespace App\Repositories\ProductGroupPriority;

use App\Models\ProductGroupPriority;
use App\Models\Store;
use App\Repositories\BaseRepository;
use App\Models\ProductGroup;
use App\Models\Product;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;
use Illuminate\Support\Carbon;
use DB;

class ProductGroupPriorityRepository extends BaseRepository implements ProductGroupPriorityRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */

    public function getModel()
    {
        return new ProductGroupPriority();
    }

    public function paginate(int $limit, array $with = [], array $args = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model
            ->with($with)
            ->when($args['product'] ?? '', function ($query) use ($args) {
                $query->where('product_id', $args['product']);
            })
            ->join('products', 'products.id', '=', 'product_group_priorities.product_id')
            ->join('users', 'users.id', '=', 'product_group_priorities.created_by')
            ->select('product_group_priorities.*', 'users.name AS user_fullname', 'users.email AS user_email',
                'products.name AS product_name', 'products.code AS product_code')
            ->orderBy('created_at', 'DESC')
            ->paginate($limit);
    }

    public function getProductGroup($bookingAt = [], $productType = null, $priority = ProductGroupPriority::PRIORITY)
    {
        if (!$bookingAt) {
            $currentDate = now()->format('Y-m-d');
            $bookingAt   = ['from' => $currentDate, 'to' => $currentDate];
        }

        return ProductGroupPriority::query()
            ->with('product', 'product.key_product', 'productGroup')
            ->when($productType, function ($query, $productType) {
                $query->where('product_type', $productType);
            })
            ->where('status', ProductGroupPriority::STATUS_ACTIVE)
            ->when($priority, function ($q) use ($priority) {
                $q->where('priority', $priority);
            })
            ->when($bookingAt['from'], function ($query) use ($bookingAt) {
                $query->where(function ($query) use ($bookingAt) {

                    $query->where(function ($query) use ($bookingAt) {
                        $query->where('period_from', '<=', $bookingAt['from'])
                            ->where(function ($query) use ($bookingAt) {
                                $query->where('period_to', '>=', $bookingAt['from'])->orWhereNull('period_to');
                            });
                    })
                        ->when($bookingAt['to'], function ($query) use ($bookingAt) {
                            $query->orWhere(function ($query) use ($bookingAt) {
                                $query->where('period_from', '<=', $bookingAt['to'])
                                    ->where(function ($query) use ($bookingAt) {
                                        $query->where('period_to', '>=', $bookingAt['to'])->orWhereNull('period_to');
                                    });
                            });
                        });
                });
            })
            ->get();
    }

    public function findByDate(array $attributes, $raw = false): array
    {
        $products = [];
        $items    = $this->getProductGroup($attributes);
        if ($raw) {
            return $items->toArray();
        }
        $items->each(function ($item) use (&$products) {
            if ($item->product->key_id) {
                $products[$item->group_id][$item->sub_group_id][$item->product->key_product->id] = $item->product->key_product->name;
            } else {
                $products[$item->group_id][$item->sub_group_id][$item->product->id] = $item->product->name;
            }
        });

        return $products;
    }

    public function findProductByDate(array $attributes, $raw = false): array
    {
        $products = [];

        $productType = (isset($attributes['product_type'])) ? $attributes['product_type'] : null;

        $items = $this->getProductGroup($attributes, $productType);
        if ($raw) {
            return $items->toArray();
        }
        $items->each(function ($item) use (&$products) {

            //dd($item->product);
            if ($item->product->key_id) {
                //dd(123);
                $products[$item->group_id][$item->sub_group_id][$item->product->key_product->id] = $item->product->key_product->name;

                //$products[$item->product_id] = $item->product->key_product->name;
            } else {
                //$products[$item->product_id] = $item->product->name;
                $products[$item->group_id][$item->sub_group_id][$item->product->id] = $item->product->name;
            }
        });

//dd($products);
        return $products;
    }

    public function getList($with = [], $requestParams = []): \Illuminate\Database\Eloquent\Collection|array
    {
        $minDate = $requestParams['minDate'] ?? null;
        $maxDate = $requestParams['maxDate'] ?? null;
        $curYear = $requestParams['curYear'] ?? Carbon::now()->format('Y');
        $query   = $this->model->with($with)
            ->where('status', ProductGroupPriority::STATUS_ACTIVE)
            ->when($requestParams['store_type'] ?? '', function ($q) use ($requestParams) {
                $q->where('store_type', $requestParams['store_type']);
            });
        if ($minDate) {
            $query->where('period_from', '<=', $minDate)
                ->where('period_to', '>=', $maxDate)
                ->orWhere(function ($query) use ($minDate, $maxDate) {
                    $query->where('period_from', '<=', $minDate)
                        ->where('period_to', null);
                });
        } else {
            $query->whereYear('period_from', $curYear)
                ->whereYear('period_to', $curYear)
                ->orWhere(function ($q) use ($curYear) {
                    $q->whereYear('period_from', $curYear)
                        ->where('period_to', null);
                });
        }
        return $query->get();
    }

    public function getListDataMultiPeriodpublic($with = [], $productType, $productId, $fromPeriods, $storeType, $regionApply)
    {
        return $this->model->with($with)
            ->when($productType, function ($query, $productType) {
                $query->where('product_type', $productType);
            })
            ->when($productId, function ($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->when($storeType, function ($query, $storeType) {
                $query->where('store_type', $storeType);
            })
            ->when($regionApply, function ($query, $regionApply) {
                $query->where('region_apply', $regionApply);
            })
            ->when($fromPeriods, function ($query) use ($fromPeriods) {
                $query->where(function ($query) use ($fromPeriods) {
                    foreach ($fromPeriods as $_key => $_fromPerriod) {
                        $query->orWhere(function ($query) use ($_fromPerriod) {
                            $query->where('period_from', '<=', $_fromPerriod);
                            $query->where(function ($query) use ($_fromPerriod) {
                                $query->whereNull('period_to');
                                $query->orWhere('period_to', '>=', $_fromPerriod);
                            });
                        });
                    }
                });
            })
            ->orderBy('period_from', 'desc')
            ->orderBy('group_id', 'asc')
            ->orderBy('sub_group_id', 'asc')
            ->orderBy('product_id', 'asc')
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
            ->when($requestParams['product'] ?? '', function ($query) use ($requestParams) {
                $query->where('product_id', $requestParams['product']);
            })
            ->join('products', 'products.id', '=', 'product_group_priorities.product_id')
            ->join('users', 'users.id', '=', 'product_group_priorities.created_by')
            ->select('product_group_priorities.*', 'users.name AS user_fullname', 'users.email AS user_email',
                'products.name AS product_name', 'products.code AS product_code');

        return $this->showOption($query, $showOption);
    }

    public function getProductGroupPriorityByProductId(
        $productType,
        $product_id,
        $productGroupPriorityId = null,
        $store_type,
        $region_apply
    ): array
    {
        return $this->model->query()
            ->where('product_type', $productType)
            ->where('product_id', $product_id)
            ->where('store_type', $store_type)
            ->where('region_apply', $region_apply)
            ->when($productGroupPriorityId ?? '', function ($query) use ($productGroupPriorityId) {
                $query->where('id', '<>', $productGroupPriorityId);
            })
            ->get()->toArray();
    }

    public function getMinMaxYear()
    {
        return $this->model->selectRaw('min(left(period_from, 4)) as minYear, max(left(period_to, 4)) as maxYear')->first()->toArray();
    }

    public function getListByGroup($subGroupId = [], $endDate = null)
    {
        return $this->model
            ->whereIn('sub_group_id', $subGroupId)
            ->where('period_to', null)
            ->orWhere(function ($query) use ($subGroupId, $endDate) {
                $query->whereIn('sub_group_id', $subGroupId)
                    ->where('period_to', '>=', $endDate);
            })
            ->get();
    }
}
