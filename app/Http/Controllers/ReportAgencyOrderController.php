<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Requests\StoreOrderBulkAction;
use App\Http\Requests\StoreOrderGetPromotion;
use App\Http\Requests\StoreOrderRequest;
use App\Repositories\StoreOrder\StoreOrderRepositoryInterface;
use App\Services\StoreOrderService;
use Illuminate\Http\Request;

class ReportAgencyOrderController extends Controller
{
    protected $repository;
    protected $storeOrderService;

    public function __construct(
        StoreOrderRepositoryInterface $repository,
        StoreOrderService             $storeOrderService
    )
    {
        $this->repository = $repository;
        $this->storeOrderService    = $storeOrderService;

        $this->middleware('permission:xem_bao_cao_ban_ke_dai_ly')->only('index');
        $this->middleware('permission:download_ban_ke_dai_ly')->only('export');
    }

    public function index(Request $request)
    {
        $indexOptions = $this->storeOrderService->reportAgencyOrderOption();
        $page_title   = '5.2.1 Bản kê ĐL';
        $search       = $request->get('search', []);
        $table        = $this->storeOrderService->getTableStatementAgency($search, $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "store_orders.created_at", "type" => "DESC"]]
        ]));

        return view('pages.reports.agency_orders_by_date', compact('indexOptions', 'search', 'table', 'page_title'));
    }

    public function export(Request $request)
    {
        $showOptions    = $request->get('options', [
            "orderBy" => ["column" => "store_orders.created_at", "type" => "DESC"]
        ]);

        return $this->storeOrderService->exportReport(
            $request->get('hash_id', null),
            $request->get('search', []),
            $showOptions
        );
    }
}
