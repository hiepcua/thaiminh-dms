<?php

namespace App\Http\Controllers;

use App\Repositories\Store\StoreRepository;
use App\Services\ReportRevenuePharmacyService;
use App\Services\ReportRevenueStoreRankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportRevenueStoreController extends Controller
{
    public function __construct(
        protected ReportRevenueStoreRankService $service,
        ReportRevenuePharmacyService $revenuePharmacyService,
        StoreRepository $storeRepository
    )
    {
        $this->storeRepository = $storeRepository;
        $this->revenuePharmacyService = $revenuePharmacyService;
        $this->middleware('can:download_bao_cao_doanh_thu_nha_thuoc')->only('export');
        $this->middleware('can:xem_bao_cao_doanh_thu_nha_thuoc')->only('pharmacyRevenue');
        $this->middleware('can:xem_bao_cao_thuong_key_qc')->only('index');
        $this->middleware('can:download_bao_cao_thuong_key_qc')->only('exportRevenue');
    }

    function index(Request $request)
    {
        $search       = $request->get('search', []);
        $table        = null;
        $hasExport = true;
        if ($search) {
            $table = $this->service->summaryRevenue($this->service->parseSearchParams($request->all()));
            $hasExport = !$table->isEmpty();
        }

        $indexOptions = $this->service->indexOptions($hasExport);

        return view('pages.reports.revenue_store.index', compact('indexOptions', 'search', 'table'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    function exportRevenue(Request $request)
    {
        $hashId = $request->get('hash_id');
        return $this->service->exportRevenue($hashId, $this->service->parseSearchParams($request->all()));
    }

    function pharmacyRevenue(Request $request)
    {
        $page_title   = '5.4.1 Doanh thu NT';
        $indexOptions = $this->revenuePharmacyService->formOptions();
//        dd($indexOptions);
        $search       = $request->get('search', []);

        $showOptions = $request->get('options', [
        ]);
        $table        = $this->revenuePharmacyService->getTableData($search, $showOptions);

        return view('pages.reports.revenue_pharmacy.list', compact('indexOptions', 'search', 'table', 'page_title'));
    }

    function detailRevenue($id)
    {
        $pharmacy = $this->storeRepository->find($id);
        $page_title   = 'Chi tiết doanh thu nhà thuốc'.$pharmacy->name;
        $from_to       = request()->from_to;

        $title_table   = 'Chi tiết doanh thu nhà thuốc'.$pharmacy->name.' từ '. str_replace('to', 'đến', $from_to) ;
        $table        = $this->revenuePharmacyService->getTableDetailData($id, $from_to, [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "store_order_items.product_type", "type" => "DESC"]]
        ]);
//        dd($indexOptions);
        return view('pages.reports.revenue_pharmacy.detail', compact(  'table', 'page_title', 'pharmacy', 'from_to', 'title_table'));

    }

    public function export(Request $request)
    {
        $showOptions    = $request->get('options', [
            "orderBy" => ["column" => "report_revenue_store.id", "type" => "DESC"]
        ]);
        return $this->revenuePharmacyService->exportReport(
            $request->get('hash_id', null),
            $request->get('search', []),
            $showOptions
        );
    }
}
