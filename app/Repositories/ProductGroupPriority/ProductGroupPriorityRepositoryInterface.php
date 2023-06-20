<?php

namespace App\Repositories\ProductGroupPriority;

use App\Repositories\BaseRepositoryInterface;

interface ProductGroupPriorityRepositoryInterface extends BaseRepositoryInterface
{
    public function findByDate(array $attributes, $raw = false): array;

    public function getProductGroup($attributes, $productType, $priority);

    public function getList($with = [], $requestParams = []): \Illuminate\Database\Eloquent\Collection|array;
}
