<?php

namespace App\Services;

use App\Repositories\ProductLog\ProductLogRepositoryInterface;
use App\Services\BaseService;
use App\Models\ProductLog;

class ProductLogService extends BaseService
{
    protected $repository;

    public function __construct(ProductLogRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    public function setModel()
    {
        return new ProductLog();
    }
}
