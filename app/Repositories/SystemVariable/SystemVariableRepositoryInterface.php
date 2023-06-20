<?php

namespace App\Repositories\SystemVariable;

use App\Repositories\BaseRepositoryInterface;

interface SystemVariableRepositoryInterface extends BaseRepositoryInterface
{
    public function findByName($name);
}
