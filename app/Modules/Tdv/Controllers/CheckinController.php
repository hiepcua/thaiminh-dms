<?php

namespace App\Modules\Tdv\Controllers;

use App\Helpers\ApiHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckinGetStoresRequest;
use App\Http\Requests\CheckinRequest;
use App\Http\Requests\CheckoutRequest;
use App\Repositories\Checkin\CheckinRepositoryInterface;
use App\Repositories\NewStore\NewStoreRepositoryInterface;
use App\Services\CheckinService;
use App\Services\ForgetCheckinService;
use App\Services\NewStoreService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckinController extends Controller
{
    protected $repository;
    protected $service;
    protected $forgetCheckoutService;

    public function __construct(
        CheckinRepositoryInterface $repository,
        CheckinService             $service,
        ForgetCheckinService       $forgetCheckinService
    )
    {
        $this->middleware('can:tdv_xem_danh_sach_nha_thuoc_checkin')->only('index');
        $this->middleware('can:tdv_checkin_tai_nha_thuoc')->only('checkin');
        $this->middleware('can:tdv_checkin_tai_nha_thuoc')->only('checkout');
        $this->middleware('can:tdv_xem_lich_su_checkin')->only('history');
        $this->middleware('can:tdv_tao_de_xuat_quen_checkout')->only('forgetCheckout');

        $this->repository            = $repository;
        $this->service               = $service;
        $this->forgetCheckoutService = $forgetCheckinService;
    }

    public function index(Request $request)
    {
        return view('Tdv::checkin.index');
    }

    public function getListStore(CheckinGetStoresRequest $request)
    {
        try {
            $lat  = $request->get('lat', null);
            $long = $request->get('long', null);

            $stores = $this->service->getStoresCheckin($lat, $long, Helper::currentUser()->id);

            return response()->json([
                'message' => 'Get list store successful',
                'stores'  => $stores,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function checkin(CheckinRequest $request)
    {
        try {
            $lat     = $request->get('lat', null);
            $long    = $request->get('long', null);
            $storeId = $request->get('store_id', null);

            $result = $this->service->checkin($lat, $long, $storeId);

            if (!$result['result']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Có lỗi xảy ra'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
                'message' => $result['message'] ?? 'Checkin thành công'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function checkout(CheckoutRequest $request)
    {
        try {
            $lat     = $request->get('lat', null);
            $long    = $request->get('long', null);
            $storeId = $request->get('store_id', null);

            $result = $this->service->checkout($lat, $long, $storeId);

            if (!$result['result']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Có lỗi xảy ra'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
                'message' => $result['message'] ?? 'Checkout thành công'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function history(Request $request)
    {
        $page_title              = 'Lịch sử checkin';
        $search                  = $request->get('search', []);
        $search['user_id']       = Helper::currentUser()->id;
        $showOptions             = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "checkin.created_at", "type" => "DESC"]]
        ]);
        $showOptionsOfRequestTab = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "forget_checkins.created_at", "type" => "DESC"]]
        ]);

        $table = $this->service->getTableTDV($search, $showOptions);

        $tableRequestCheckin = $this->service->getTableRequestTab(
            $search,
            $showOptionsOfRequestTab,
            'request-checkout-list-tdv'
        );

        return view('Tdv::checkin.history', compact(
            'table',
            'page_title',
            'tableRequestCheckin'
        ));
    }

    public function forgetCheckout(Request $request)
    {
        try {
            $checkinId   = $request->get('checkinId', null);
            $creatorNote = $request->get('creatorNote', null);

            $result = $this->forgetCheckoutService->forgetCheckout($checkinId, $creatorNote);

            if (!$result['result']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Có lỗi xảy ra'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
                'message' => $result['message'] ?? 'Tạo đề xuất thành công'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }
}
