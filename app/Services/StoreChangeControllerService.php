<?php

namespace App\Services;

use App\Repositories\StoreChangeController\StoreChangeControllerRepositoryInterface;
use App\Services\BaseService;
use App\Models\StoreChangeController;

class StoreChangeControllerService extends BaseService
{
    protected $repository;

    public function __construct(StoreChangeControllerRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    public function setModel()
    {
        return new StoreChangeController();
    }
}
