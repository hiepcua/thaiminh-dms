<?php

namespace App\Services;

use App\Repositories\PromotionCondition\PromotionConditionRepositoryInterface;
use App\Services\BaseService;
use App\Models\PromotionCondition;

class PromotionConditionService extends BaseService
{
    protected $repository;

    public function __construct(PromotionConditionRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    public function setModel()
    {
        return new PromotionCondition();
    }
}
