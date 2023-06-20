<?php

namespace App\Services;

use App\Repositories\LineGroup\LineGroupRepositoryInterface;
use App\Services\BaseService;
use App\Models\LineGroup;

class LineGroupService extends BaseService
{
    protected $repository;

    public function __construct(LineGroupRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    public function setModel()
    {
        return new LineGroup();
    }
}
