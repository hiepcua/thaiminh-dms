<?php

namespace App\Modules\Tdv\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    protected DashboardService $service;

    public function __construct(DashboardService $service)
    {
        $roleTdv = User::ROLE_TDV;
        $this->service = $service;

        $this->middleware(["role:$roleTdv"])->only('index');
    }

    function index()
    {
        $page_title = "Tổng quan TDV";
        $formOption = $this->service->formOptions();
        $data = $this->service->getDataForTdv(request('search', []));

        return view('Tdv::dashboard.index', compact('page_title', 'formOption', 'data'));
    }
}
