<?php

namespace App\Services;

use App\Repositories\District\DistrictRepositoryInterface;
use App\Repositories\Province\ProvinceRepositoryInterface;
use App\Models\Province;
use App\Repositories\Ward\WardRepositoryInterface;

class ProvinceService extends BaseService
{
    protected $repository;
    protected $districtRepository;
    protected $wardRepository;

    public function __construct(
        ProvinceRepositoryInterface $repository,
        DistrictRepositoryInterface $districtRepository,
        WardRepositoryInterface     $wardRepository
    )
    {
        parent::__construct();

        $this->repository         = $repository;
        $this->districtRepository = $districtRepository;
        $this->wardRepository     = $wardRepository;
    }

    public function setModel()
    {
        return new Province();
    }

    public function getDistricts($provinceId): array
    {
        if (!$provinceId) {
            return [];
        }
        return $this->districtRepository->getByProvince($provinceId);
    }

    public function getWards($districtId): array
    {
        if (!$districtId) {
            return [];
        }
        return $this->wardRepository->getByDistrict($districtId);
    }
}
