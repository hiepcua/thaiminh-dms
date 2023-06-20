<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Http\Requests\CreateEditStoreRequest;
use App\Models\StoreChange;
use App\Repositories\StoreChange\StoreChangeRepositoryInterface;
use App\Services\StoreChangeService;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Http\Response;

class StoreChangeController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(
        StoreChangeRepositoryInterface $repository,
        StoreChangeService             $service
    )
    {
        $this->repository = $repository;
        $this->service    = $service;
        $this->middleware('can:xem_nha_thuoc_thay_doi')->only('index');
        $this->middleware('can:duyet_nha_thuoc_thay_doi')->only('edit', 'update');
    }

    public function index(Request $request)
    {
        $indexOptions = $this->service->indexOptions();
        $search       = $request->get('search', []);
        $showOptions  = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "store_changes.created_at", "type" => "DESC"]]
        ]);
        $table        = $this->service->getTable($search, $showOptions);

        return view('pages.store_changes.index', compact('table', 'indexOptions'));
    }

    public function store(Request $request)
    {
        $attributes = $request->all();
        $item       = $this->repository->create($attributes);
    }

    public function show($id)
    {
        $storeChange = $this->repository->findOrFail($id);
        $accessRight = $this->service->checkLocalityPermission($storeChange);
        if (!$accessRight) return redirect()->route($this->service->urlRedirect());
        $formOptions    = $this->service->formOptions($storeChange, $id);
        $default_values = $formOptions['default_values'];
        $current_store  = $formOptions['current_store'];
        return view('pages.store_changes.view', compact('formOptions', 'default_values', 'current_store'));
    }

    public function edit($id)
    {
        $storeChange = $this->repository->findOrFail($id);
        $accessRight = $this->service->checkLocalityPermission($storeChange);
        if (!$accessRight) return redirect()->route($this->service->urlRedirect());
        if ($storeChange->status == StoreChange::STATUS_INACTIVE) {
            $formOptions           = $this->service->formOptions($storeChange);
            $formOptions['action'] = route('admin.store_changes.update', $id);
            $default_values        = $formOptions['default_values'];
            $new_store_id          = $id;
            $provinces             = $formOptions['provinces'];
            $districts             = $formOptions['districts'] ?? [];
            $wards                 = $formOptions['wards'] ?? [];

            return view('pages.store_changes.edit', compact(
                'formOptions',
                'default_values',
                'new_store_id',
                'provinces',
                'districts',
                'wards',
            ));
        } else {
            return redirect()->route('admin.store_changes.index');
        }
    }

    public function update(CreateEditStoreRequest $request, $id)
    {
        $attributes = $request->all();
        $this->service->update($id, $attributes);
        Helper::successMessage('Duyệt nhà thuốc thay đổi thành công.');
        return redirect()->route('admin.store_changes.index');
    }

    public function approve($id)
    {
        $storeChange = $this->repository->findOrFail($id);
        $accessRight = $this->service->checkLocalityPermission($storeChange);
        if (!$accessRight) return redirect()->route($this->service->urlRedirect());
        $formOptions           = $this->service->formOptions($storeChange, $id);
        $formOptions['action'] = route('admin.store_changes.update-approve', $id);
        $default_values        = $formOptions['default_values'];
        $current_store         = $formOptions['current_store'];

        return view('pages.store_changes.approve', compact('formOptions', 'default_values', 'current_store'));
    }

    public function updateApprove(Request $request, $id)
    {
        $attributes = $request->all();
        $this->service->approve($id, $attributes);
        if ($attributes['status'] == StoreChange::STATUS_ACTIVE) {
            Helper::successMessage('Duyệt nhà thuốc thay đổi thành công.');
        } else {
            Helper::successMessage('Cập nhật nhà thuốc thay đổi thành công.');
        }
        return redirect()->route('admin.store_changes.index');
    }

    public function destroy($id)
    {
        $this->repository->delete($id);
    }

    public function notApprove(Request $request)
    {
        $storeChangeId = $request->get('storeChangeId');
        $reason        = $request->get('reason');
        try {
            $result = $this->repository->notApprove($storeChangeId, $reason);
            if ($result) {
                return response()->json([
                    'htmlString' => 'success',
                ], Response::HTTP_OK);
            }
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }
}
