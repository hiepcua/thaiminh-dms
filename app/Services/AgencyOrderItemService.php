<?php

namespace App\Services;

use App\Repositories\AgencyOrderItem\AgencyOrderItemRepositoryInterface;
use App\Services\BaseService;
use App\Models\AgencyOrderItem;

class AgencyOrderItemService extends BaseService
{
    protected $repository;

    public function __construct(AgencyOrderItemRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    public function setModel()
    {
        return new AgencyOrderItem();
    }
}
