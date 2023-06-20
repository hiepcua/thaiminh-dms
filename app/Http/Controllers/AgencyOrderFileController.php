<?php

namespace App\Http\Controllers;

use App\Repositories\AgencyOrderFile\AgencyOrderFileRepositoryInterface;
use App\Services\AgencyOrderFileService;
use App\Services\StoreOrderService;
use Illuminate\Http\Request;

class AgencyOrderFileController extends Controller
{
    protected $repository;
    protected $service;
    protected $storeOrderService;

    public function __construct(
        AgencyOrderFileRepositoryInterface $repository,
        AgencyOrderFileService $service,
        StoreOrderService $storeOrderService
    ) {
        $this->repository = $repository;
        $this->service    = $service;
        $this->storeOrderService = $storeOrderService;

        $this->middleware('permission:xem_danh_sach_ban_in_phieu_xuat_kho')->only('index');
    }

    public function index(Request $request)
    {
        $indexOptions = $this->service->formOption();
        $page_title   = '5.2.1 Bản kê ĐL';
        $search       = $request->get('search', []);
        $table        = $this->service->getTableStatementAgency($search, $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "agency_order_files.created_at", "type" => "DESC"]]
        ]));

        return view('pages.reports.print_pxk', compact('indexOptions', 'search', 'table', 'page_title'));
    }
}
