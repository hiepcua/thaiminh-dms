<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Helpers\Helper;
use App\Helpers\TableHelper;
use App\Http\Requests\SACreateEditAgencyOrderRequest;
use App\Models\AgencyOrder;
use App\Models\User;
use App\Repositories\AgencyOrder\AgencyOrderRepositoryInterface;
use App\Services\AgencyOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AgencyOrderController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(
        AgencyOrderRepositoryInterface $repository,
        AgencyOrderService $service
    ) {
        $this->repository = $repository;
        $this->service    = $service;

        $this->middleware('permission:xem_danh_sach_don_nhap_dai_ly')->only('index');
        $this->middleware('permission:huy_don_nhap_dai_ly')->only('removeOrder');
        $this->middleware('permission:them_don_nhap_dai_ly')->only('create', 'store');
        $this->middleware('permission:sua_don_nhap_dai_ly')->only('update', 'edit');
        $this->middleware('permission:xem_chi_tiet_don_nhap_dai_ly')->only('show', 'showOrderTdv');
        $this->middleware('permission:download_don_nhap_dai_ly')->only('export');
    }

    public function index(Request $request)
    {
        $page_title     = '3.2 Danh sách đơn nhập của đại lý';
        $search         = $request->get('search', []);
        $showOptions    = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "agency_orders.created_at", "type" => "DESC"]]
        ]);

        $formOptions    = $this->service->formOptions();
        $defaultValues  = $formOptions['default_values'] ?? [];
        $table = $this->service->getTable($search, $showOptions);

        return view('pages.agency_order.index', compact(
            'page_title',
            'formOptions',
            'defaultValues',
            'table')
        );
    }

    public function create()
    {
        $page_title            = '3.2 Sale admin lên đơn cho đại lý';
        $formOptions           = $this->service->formOptions();
        $formOptions['action'] = route('admin.agency-order.store');
        $default_values        = $formOptions['default_values'] ?? [];

        return view('pages.agency_order.create_or_edit', compact(
            'page_title',
            'formOptions',
            'default_values'
        ));
    }

    public function store(SACreateEditAgencyOrderRequest $request)
    {
        $attributes = $request->all();

        $result = $this->service->storeAgencyOrder($attributes);

        if (!$result) {
            return redirect()->route('admin.agency-order.index')->with('errorMessage', 'Tạo đơn hàng không thành công. Vui lòng thử lại.');
        }

        return redirect()->route('admin.agency-order.index')->with('successMessage', 'Dơn hàng đã được tạo thành công!');
    }

    public function show($id)
    {
        $page_title            = '3.2 Xem thông tin sale admin lên đơn đại lý';
        $agencyOrder           = $this->repository->find($id, ['agency', 'agencyOrderItems']);
        $formOptions           = $this->service->formOptions($agencyOrder);
        $default_values        = $formOptions['default_values'] ?? [];

        return view('pages.agency_order.create_or_edit', compact(
            'page_title',
            'formOptions',
            'default_values'
        ));
    }

    public function showOrderTdv($id)
    {
        $pageTitle = "3.2 Xem đơn nhập bởi TDV";
        $agencyOrder = $this->service->getAgencyTdvOrder($id);

        if (!$agencyOrder) {
            return redirect()->route('admin.agency-order.index')->with('errorMessage', 'Đơn hàng không tồn tại. Vui lòng thử lại.');
        }

        return view('pages.agency_order.show_agency_tdv_order', compact('agencyOrder', 'pageTitle'));
    }

    public function edit($id)
    {
        $page_title            = '3.2 Xem thông tin sale admin lên đơn đại lý';
        $agencyOrder           = $this->repository->find($id, ['agency', 'agencyOrderItems']);
        $formOptions           = $this->service->formOptions($agencyOrder);
        $formOptions['action'] = route('admin.agency-order.update', $id);
        $default_values        = $formOptions['default_values'] ?? [];

        if ($agencyOrder->status != AgencyOrder::STATUS_CHUA_KC) {
            return redirect()->route('admin.agency-order.index')->with('errorMessage', 'Đơn hàng đã kết chuyển không thể edit.');
        }

        return view('pages.agency_order.create_or_edit', compact(
            'page_title',
            'formOptions',
            'default_values'
        ));
    }

    public function update(SACreateEditAgencyOrderRequest $request, $id)
    {
        $attributes = $request->all();

        $result = $this->service->updateAgencyOrder($id, $attributes);

        if (!$result) {
            return redirect()->route('admin.agency-order.index')->with('errorMessage', 'Update đơn hàng không thành công. Vui lòng thử lại.');
        }

        return redirect()->route('admin.agency-order.index')->with('successMessage', 'Update Đơn hàng thành công!');
    }

    public function destroy($id)
    {
        $this->repository->delete($id);
    }

    public function export(Request $request)
    {
        $showOptions    = $request->get('options', [
            "orderBy" => ["column" => "agency_orders.created_at", "type" => "DESC"]
        ]);

        return $this->service->export(
            $request->get('hash_id', null),
            $request->get('search', []),
            $showOptions
        );
    }

    public function checkOrderAllowDelete(Request $request)
    {
        try {
            $agencyOrderIds = $request->get('ids', []);

            $result = $this->service->isAllowUpdateToDelete($agencyOrderIds);

            return response()->json([
                'result' => $result
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function removeOrder(Request $request)
    {
        try {
            $agencyOrderIds = $request->get('ids', []);

            $result = $this->service->removeOrder($agencyOrderIds);

            if ($result) {
                return response()->json([
                    'message' => 'Đơn hàng đã được hủy thành công',
                    'result' => true
                ], Response::HTTP_OK);
            }

            return response()->json([
                'message' => 'Hủy đơn hàng không thành công',
                'result' => false
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }
}
