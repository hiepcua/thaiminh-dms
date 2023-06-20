<?php

namespace App\Http\Controllers;

use App\Repositories\Province\ProvinceRepositoryInterface;
use App\Services\ProvinceService;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(
        ProvinceRepositoryInterface $repository,
        ProvinceService             $service
    )
    {
        $this->repository = $repository;
        $this->service    = $service;
    }

    public function getByType(Request $request, $type): \Illuminate\Http\JsonResponse
    {
        $output = [];
        if ($type == 'districts') {
            $provinceId = $request->get('province_id');
            $output     = $this->service->getDistricts($provinceId);
        } elseif ($type == 'wards') {
            $districtId = $request->get('district_id');
            $output     = $this->service->getWards($districtId);
        }
        return response()->json($output);
    }
}
