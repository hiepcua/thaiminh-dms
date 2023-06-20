<?php

namespace App\Modules\Tdv\Controllers;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEditStoreRequest;
use App\Repositories\Checkin\CheckinRepositoryInterface;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Services\CheckinService;
use App\Services\StoreOrderService;
use App\Services\StoreService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    protected $repository;
    protected $service;
    protected $storeOrderService;
    protected $checkinService;
    protected $checkinRepository;

    public function __construct(
        StoreRepositoryInterface   $repository,
        StoreService               $service,
        StoreOrderService          $storeOrderService,
        CheckinService             $checkinService,
        CheckinRepositoryInterface $checkinRepository
    )
    {
        $this->repository        = $repository;
        $this->service           = $service;
        $this->storeOrderService = $storeOrderService;
        $this->checkinService    = $checkinService;
        $this->checkinRepository = $checkinRepository;

        $this->middleware('permission:xem_nha_thuoc|tdv_xem_nha_thuoc')->only('index');
    }

    public function index(Request $request)
    {
        $indexOptions  = $this->service->indexOptions();
        $search        = $request->get('search', []);
        $formOptions   = $this->service->formOptionsTDV();
        $showOptions   = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "stores.created_at", "type" => "DESC"]]
        ]);
        $defaultValues = $formOptions['default_values'] ?? [];
        $table         = $this->service->getTableForTdv($search, $showOptions);

        return view('Tdv::stores.index', compact('table', 'formOptions', 'defaultValues', 'indexOptions'));
    }

    public function turnover($storeId, Request $request)
    {
        $page_title  = "Doanh thu nhà thuốc";
        $dataRequest = request('search', []);
        $data        = $this->storeOrderService->getDataTurnover($storeId, $dataRequest);

        return view('Tdv::stores.turnover', compact('page_title', 'data'));
    }

    public function create()
    {
        $store                 = $this->service->getModel();
        $formOptions           = $this->service->formOptionsTDV($store);
        $formOptions['action'] = route('admin.tdv.store.store');
        $default_values        = $formOptions['default_values'];
        $localities            = $formOptions['localities'];
        $provinces             = $formOptions['provinces'];

        return view('Tdv::stores.add', compact(
            'formOptions',
            'default_values',
            'localities',
            'provinces',
        ));
    }

    public function store(CreateEditStoreRequest $request)
    {
        $this->service->create($request->all());
        Helper::successMessage('Thêm nhà thuốc thành công. Sau khi SA duyệt thì sẽ hiển thị lên phần danh sách nhà thuốc của bạn.');
        return redirect()->route('admin.tdv.new-stores.index');
    }

    public function show($id)
    {
        $store       = $this->repository->findOrFail($id);
        $accessRight = $this->service->checkLocalityPermission($store);
        if (!$accessRight) return redirect()->route($this->service->urlRedirect());
        $formOptions               = $this->service->formOptionsTDV($store);
        $default_values            = $formOptions['default_values'];
        $localities                = $formOptions['localities'];
        $provinces                 = $formOptions['provinces'];
        $currentTdvId              = Helper::currentUser()->id;
        $storeCheckedInDay         = $this->checkinService
            ->getStoresChecked($currentTdvId)
            ->pluck('checkin_at', 'store_id')
            ->toArray();
        $checkedRecordCurrentStore = $this->checkinRepository->getToDayChecked($id, $currentTdvId);

        return view('Tdv::stores.show', compact(
            'formOptions',
            'default_values',
            'localities',
            'provinces',
            'id',
            'storeCheckedInDay',
            'checkedRecordCurrentStore'
        ));
    }

    public function edit($id)
    {
        $store       = $this->repository->findOrFail($id);
        $accessRight = $this->service->checkLocalityPermission($store);
        if (!$accessRight) return redirect()->route($this->service->urlRedirect());
        $formOptions           = $this->service->formOptionsTDV($store);
        $formOptions['action'] = route('admin.tdv.store.update', $id);
        $default_values        = $formOptions['default_values'];
        $localities            = $formOptions['localities'] ?? null;
        $provinces             = $formOptions['provinces'] ?? null;
        $districts             = $formOptions['districts'] ?? null;
        $wards                 = $formOptions['wards'] ?? null;
        $storeId               = $id;

        return view('Tdv::stores.edit', compact(
            'formOptions',
            'default_values',
            'localities',
            'provinces',
            'districts',
            'wards',
            'storeId',
        ));
    }

    public function update(CreateEditStoreRequest $request, $id)
    {
        $attributes = $request->all();
        $this->service->update($id, $attributes);
        Helper::successMessage('Cập nhật thay đổi thông tin nhà thuốc thành công. Sau khi SA duyệt thì sẽ cập nhật lại thông tin nhà thuốc.');
        return redirect()->route('admin.tdv.store-changes.index');
    }
}
