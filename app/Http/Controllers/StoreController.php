<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Models\NewStore;
use App\Models\Organization;
use App\Models\Store;
use App\Models\User;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Http\Requests\CreateEditStoreRequest;
use App\Services\StoreService;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Http\Response;

class StoreController extends Controller
{
    protected $repository;

    public function __construct(
        StoreRepositoryInterface $repository,
        StoreService             $service
    )
    {
        $this->repository = $repository;
        $this->service    = $service;

        $this->middleware('permission:xem_nha_thuoc|tdv_xem_nha_thuoc')->only('index', 'show');
        $this->middleware('permission:them_nha_thuoc|tdv_them_nha_thuoc')->only('create', 'store');
        $this->middleware('permission:sua_nha_thuoc|tdv_sua_nha_thuoc')->only('edit', 'update');
    }

    public function index(Request $request)
    {
        $indexOptions = $this->service->indexOptions();
        $search       = $request->get('search', []);
        $showOptions  = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "stores.created_at", "type" => "DESC"]]
        ]);
        $table        = $this->service->getTable($search, $showOptions);
        $isAddNew     = Helper::userCan('them_nha_thuoc') || Helper::userCan('tdv_them_nha_thuoc');

        return view('pages.stores.index', compact('table', 'indexOptions', 'isAddNew'));
    }

    public function create()
    {
        $store                 = $this->service->getModel();
        $formOptions           = $this->service->formOptions($store);
        $formOptions['action'] = route('admin.stores.store');
        $default_values        = $formOptions['default_values'];
        $localities            = $formOptions['localities'];
        $provinces             = $formOptions['provinces'];

        return view('pages.stores.add-edit', compact(
            'formOptions',
            'default_values',
            'localities',
            'provinces',
        ));
    }

    public function store(CreateEditStoreRequest $request)
    {
        $attributes    = $request->all();
        $routeRedirect = Helper::currentUser()->hasRole(User::ROLE_TDV)
            ? 'admin.new-stores.index'
            : 'admin.stores.index';
        $this->service->create($attributes);
        Helper::currentUser()->hasRole(User::ROLE_TDV) ?
            Helper::successMessage('Thêm nhà thuốc thành công. Sau khi SA duyệt thì sẽ hiển thị lên phần danh sách nhà thuốc của bạn.') :
            Helper::successMessage('Thêm nhà thuốc thành công.');
        return redirect()->route($routeRedirect);
    }

    public function show($id)
    {
        $storeModel  = $this->repository->findOrFail($id, ['line']);
        $accessRight = $this->service->checkLocalityPermission($storeModel);
        if ($accessRight) {
            $formOptions    = $this->service->formOptions($this->repository->findOrFail($id));
            $default_values = $formOptions['default_values'];
            $localities     = $formOptions['localities'];
            $provinces      = $formOptions['provinces'];
            $districts      = $formOptions['districts'] ?? [];
            $wards          = $formOptions['wards'] ?? [];
            $storeId        = $id;

            return view('pages.stores.view', compact(
                'formOptions',
                'default_values',
                'localities',
                'provinces',
                'districts',
                'wards',
                'storeId',
            ));
        } else {
            return redirect()->route($this->service->urlRedirect());
        }
    }

    public function edit($id)
    {
        $storeModel  = $this->repository->findOrFail($id, ['line']);
        $accessRight = $this->service->checkLocalityPermission($storeModel);
        if ($accessRight) {
            $formOptions           = $this->service->formOptions($storeModel);
            $formOptions['action'] = route('admin.stores.update', $id);
            $default_values        = $formOptions['default_values'];
            $localities            = $formOptions['localities'];
            $provinces             = $formOptions['provinces'];
            $districts             = $formOptions['districts'] ?? [];
            $wards                 = $formOptions['wards'] ?? [];
            $storeId               = $id;

            return view('pages.stores.add-edit', compact(
                'formOptions',
                'default_values',
                'localities',
                'provinces',
                'districts',
                'wards',
                'storeId',
            ));
        } else {
            return redirect()->route($this->service->urlRedirect());
        }
    }

    public function update(CreateEditStoreRequest $request, $id)
    {
        $attributes    = $request->all();
        $routeRedirect = Helper::currentUser()->hasRole(User::ROLE_TDV)
            ? 'admin.store_changes.index'
            : 'admin.stores.index';
        $this->service->update($id, $attributes);
        Helper::successMessage('Cập nhật nhà thuốc thành công.');
        return redirect()->route($routeRedirect);
    }

    public function destroy($id)
    {
        $this->repository->delete($id);
    }

    public function listStore(Request $request)
    {
        $name     = $request->name ?? '';
        $province = $request->province ?? '';
        $district = $request->district ?? '';
        $result   = $this->repository->getList($province, $district, $name);
        $html     = view('pages.stores.list-store', compact('result'))->render();
        return $html;
    }

    public function getStoreById(Request $request)
    {
        return Store::query()->find($request->store_id ?? '')->toArray();
    }

    public function getStoreDuplicate(Request $request)
    {
        $attributes                = [];
        $attributes['name']        = $request->name ?? '';
        $attributes['code']        = $request->code ?? '';
        $attributes['address']     = $request->address ?? '';
        $attributes['phone_owner'] = $request->phone_owner ?? '';
        $attributes['locality_id'] = $request->locality ?? '';
        $attributes['wardId']      = $request->wardId ?? '';
        $attributes['vat_number']  = $request->vat_number ?? '';
        $attributes['excludeId']   = $request->excludeId ?? '';

        return $this->service->checkStoreExist($attributes);
    }

    public function generationCode(Request $request)
    {
        $provinceId = $request->get("provinceId") ?? '';
        $districtId = $request->get("districtId") ?? '';
        $storeType  = $request->get("storeType") ?? '';

        return $this->service->generateCode($provinceId, $districtId, $storeType);
    }

    public function getByLocality(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $result = $this->repository->getByLocality($request->get('locality_id', default: null));
            $html   = view('pages.stores.list-store-locality', compact('result'))->render();
            return response()->json([
                'htmlString' => $html,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }
}
