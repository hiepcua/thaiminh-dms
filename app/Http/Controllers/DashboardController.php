<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerSummary;
use App\Models\MongoDB\SmsMo;
use App\Models\User;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    protected $service;

    public function __construct(DashboardService $service)
    {
        $roleAdmin = User::ROLE_Admin;
        $roleTdv = User::ROLE_TDV;
        $this->service = $service;
    }

    function index()
    {
        if (request()->user()->hasRole(User::ROLE_TDV)) {
            return redirect()->route('admin.tdv.dashboard');
        }

        $page_title = "Tổng quan";

        $mo_chua_xu_ly    = 0;//SmsMo::query()->where('status', 0)->count();
        $mo_chua_xu_ly    = number_format($mo_chua_xu_ly, 0, ',', '.');
        $customer_total   = 0;//Customer::query()->count();
        $customer_total   = number_format($customer_total, 0, ',', '.');
        $product_total    = 0;//Product::query()->count();
        $product_total    = number_format($product_total, 0, ',', '.');
        $wrong_data_total = 0;//CustomerSummary::query()->where('wrong_data', '>', 0)->count();
        $wrong_data_total = number_format($wrong_data_total, 0, ',', '.');

        return view('pages.dashboard.index', compact('mo_chua_xu_ly', 'customer_total', 'product_total', 'wrong_data_total'))
            ->with('page_title',$page_title);

    }
}
