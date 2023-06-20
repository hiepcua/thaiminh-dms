<?php

namespace App\Http\Controllers;

use App\Exports\TDVSummaryExport;
use App\Services\ReportRevenueTDVService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ReportRevenueTDVController extends Controller
{
    protected $service;

    public function __construct(
        ReportRevenueTDVService $service,
    )
    {
        $this->service = $service;

        $this->middleware('can:xem_bao_cao_doanh_thu_tdv')->only('index');
        $this->middleware('can:download_bao_cao_doanh_thu_tdv')->only('summaryExport');
        $this->middleware('can:xem_bao_cao_doanh_thu_chi_tiet_tdv')->only('detail');
        $this->middleware('can:xem_bao_cao_doanh_thu_chi_tiet_tdv')->only('exportAsmDetail');
//        $this->middleware('can:download_bao_cao_doanh_thu_chi_tiet_tdv')->only('detailExport');
    }

    function index(Request $request)
    {
        $indexOptions  = $this->service->indexOptions();
        $summaryValues = $this->service->summaryRevenue($request->all());

        return view('pages.reports.revenue_tdv.index', compact('indexOptions', 'summaryValues'));
    }

    function summaryExport(Request $request)
    {
        return response()->json($this->service->summaryExport($request->all()));
    }

    function detail(Request $request)
    {
        $indexOptions = $this->service->detailOptions();
        list('header' => $header, 'rows' => $rows) = $this->service->detailRevenue($request->all());

        return view('pages.reports.revenue_tdv.detail', compact('indexOptions', 'header', 'rows'));
    }

    function exportAsmDetail(Request $request)
    {
        return $this->service->asmRevenueDetailExport($request->all());
    }
//
//    function detailExport(Request $request)
//    {
//        return response()->json($this->service->summaryExport($request->all()));
//    }
}
