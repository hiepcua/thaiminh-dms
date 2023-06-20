<?php

namespace App\Repositories\Line;

use App\Repositories\BaseRepositoryInterface;

interface LineRepositoryInterface extends BaseRepositoryInterface
{
    public function getByRequest($with = [], $requestParams = [], $showOption = []);

    public function getForListScreen($requestParams = [], $showOption = []);
}
