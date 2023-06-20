<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Models\StoreOrder;
use App\Repositories\StoreOrder\StoreOrderRepositoryInterface;
use App\Services\AgencyOrderService;
use App\Services\StoreOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AgencyTDVOrderController extends Controller
{
    protected $repository;
    protected $service;
    protected $agencyOrderService;

    public function __construct(
        StoreOrderRepositoryInterface $repository,
        StoreOrderService $service,
        AgencyOrderService $agencyOrderService
    ) {
        $this->repository = $repository;
        $this->service    = $service;
        $this->agencyOrderService = $agencyOrderService;

        $this->middleware('permission:xem_danh_sach_don_nhap_cua_tdv_dai_ly')->only('index');
        $this->middleware('permission:download_don_tdv_toi_dai_ly')->only('export');
        $this->middleware('permission:them_don_nhap_tdv_toi_dai_ly')->only('create', 'store');
    }

    public function index(Request $request)
    {
        $page_title            = '3.3 Đơn nhập của TDV tới đại lý';
        $search         = $request->get('search', []);
        $showOptions    = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "store_orders.created_at", "type" => "DESC"]]
        ]);

        $formOptions    = $this->service->formOptionsForAgencyTDVOrder();
        $defaultValues  = $formOptions['default_values'] ?? [];
        $table = $this->service->getTableForAgencyTDVOrder($search, $showOptions);

        return view('pages.agency_tdv_order.index', compact(
            'formOptions',
            'defaultValues',
            'page_title',
            'table')
        );
    }

    public function create(Request $request)
    {
        //
    }

    public function getValidateBeforeCreate()
    {
        return redirect()->route('admin.agency-order-tdv.index');
    }

    public function validateBeforeCreate(Request $request) {
        $storeOrderIds = $request->get('ids', []);

        if (!count($storeOrderIds)) {
            return redirect()->route('admin.agency-order-tdv.index')
                ->with('errorMessage', 'Cần phải chọn đơn hàng trước khi thanh toán đơn cho đại lý.');
        }

        if (!$this->service->isAllowToCreateOrder($storeOrderIds)) {
            return redirect()->route('admin.agency-order-tdv.index')
                ->with('errorMessage', 'Đơn hàng được chọn để thanh toán không hợp lệ.');
        }

        $ordersByTDV = $this->repository->getByArrId($storeOrderIds, ['agency']);
        foreach ($ordersByTDV as $order) {
            if ($order->agency?->order_code == '') {
                return redirect()->route('admin.agency-order-tdv.index')
                    ->with('errorMessage', 'Đơn hàng được chọn thuộc đại lý chưa có mã số đơn thanh toán. Đại lý: ' . $order->agency?->name);
            }
        }

        return $this->showCreateAgencyOrder($storeOrderIds);
    }

    protected function showCreateAgencyOrder($storeOrderIds)
    {
        $page_title = '3.4 Xác nhận đơn ĐL thanh toán';

        if (!count($storeOrderIds)) {
            return redirect()->route('admin.agency-order-tdv.index')
                ->with('errorMessage', 'Cần phải chọn đơn hàng trước khi thanh toán đơn cho đại lý.');
        }

        if (!$this->service->isAllowToCreateOrder($storeOrderIds)) {
            return redirect()->route('admin.agency-order-tdv.index')
                ->with('errorMessage', 'Đơn hàng được chọn để thanh toán không hợp lệ.');
        }

        $agencyStoreOrders = $this->service->getAgencyStoreOrder($storeOrderIds);

        return view('pages.agency_tdv_order.create_agency_order', compact('agencyStoreOrders', 'page_title', 'storeOrderIds'));
    }

    public function checkOrderAllowCreate(Request $request)
    {
        try {
            $storeOrderIds = $request->get('ids', []);

            if (!count($storeOrderIds)) {
                return response()->json([
                    'result' => false
                ], Response::HTTP_OK);
            }

            $result = $this->service->isAllowToCreateOrder($storeOrderIds);

            return response()->json([
                'result' => $result
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function storeAgencyOrder(Request $request)
    {
        if (!$request->get('booking_at')) {
            redirect()->back()->with('errorMessage', 'Ngày sinh mã không được để trống.');
        }

        $storeOrderIds = $request->get('storeOrder', []);
        $bookingAt = $request->get('booking_at', now()->format('Y-m-d'));

        $result = $this->agencyOrderService->storeByTdvOrder($storeOrderIds, $bookingAt);

        if ($result) {
            return redirect()->route('admin.agency-order-tdv.index')
                ->with('successMessage', 'Đơn hàng được chọn đã thanh toán thành công.');
        }

        return redirect()->route('admin.agency-order-tdv.index')
            ->with('errorMessage', 'Đơn hàng được chọn thanh toán không thành công.');
    }

    public function store(Request $request)
    {
        $attributes = $request->all();
        $item       = $this->repository->create($attributes);
    }

    public function show($id)
    {
        $item = $this->repository->find($id);
    }

    public function edit($id)
    {
        $item = $this->repository->find($id);
    }

    public function update(Request $request, $id)
    {
        $attributes = $request->all();
        $item       = $this->repository->update($id, $attributes);
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
            $showOptions,
            'agency_order_tdv'
        );
    }
}
