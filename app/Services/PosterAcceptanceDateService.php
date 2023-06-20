<?php

namespace App\Services;

use App\Repositories\PosterAcceptanceDate\PosterAcceptanceDateRepositoryInterface;
use App\Services\BaseService;
use App\Models\PosterAcceptanceDate;

class PosterAcceptanceDateService extends BaseService
{
    protected $repository;

    public function __construct(PosterAcceptanceDateRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    public function setModel()
    {
        return new PosterAcceptanceDate();
    }
}
