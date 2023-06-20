<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Helpers\Helper;
use App\Http\Requests\ApproveNewStoreRequest;
use App\Http\Requests\CreateEditStoreRequest;
use App\Models\NewStore;
use App\Repositories\NewStore\NewStoreRepositoryInterface;
use App\Services\NewStoreService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NewStoreController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(
        NewStoreRepositoryInterface $repository,
        NewStoreService             $service
    )
    {
        $this->repository = $repository;
        $this->service    = $service;
        $this->middleware('can:xem_nha_thuoc_moi')->only('index');
        $this->middleware('can:duyet_nha_thuoc_moi')->only('edit', 'update');
    }

    public function index(Request $request)
    {
        $indexOptions = $this->service->indexOptions();
        $search       = $request->get('search', []);
        $showOptions  = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "new_stores.created_at", "type" => "DESC"]]
        ]);
        $table        = $this->service->getTable($search, $showOptions);

        return view('pages.new_stores.index', compact('table', 'indexOptions'));
    }

    public function show($id)
    {
        $newStore    = $this->repository->findOrFail($id);
        $accessRight = $this->service->checkLocalityPermission($newStore);
        if (!$accessRight) return redirect()->route($this->service->urlRedirect());
        $formOptions    = $this->service->formOptions($newStore);
        $default_values = $formOptions['default_values'];
        return view('pages.new_stores.view', compact('default_values', 'formOptions'));
    }

    public function approve($id)
    {
        $newStore    = $this->repository->findOrFail($id);
        $accessRight = $this->service->checkLocalityPermission($newStore);
        if (!$accessRight) return redirect()->route($this->service->urlRedirect());
        $formOptions           = $this->service->formOptions($newStore);
        $formOptions['action'] = route('admin.new-stores.update-approve', $id);
        $default_values        = $formOptions['default_values'];
        $new_store_id          = $id;
        $currentUser           = Helper::currentUser();
        $canEditNewStore       = $currentUser->can('duyet_nha_thuoc_moi');

        return view('pages.new_stores.approve', compact('formOptions', 'default_values', 'new_store_id', 'canEditNewStore'));
    }

    public function updateApprove(ApproveNewStoreRequest $request, $id)
    {
        $attributes = $request->all();
        $this->service->approve($id, $attributes);
        if ($attributes['storeStatus'] == NewStore::STATUS_ACTIVE) {
            Helper::successMessage('Duyệt nhà thuốc mới thành công.');
        } else {
            Helper::successMessage('Cập nhật nhà thuốc mới thành công.');
        }
        return redirect()->route('admin.new-stores.index');
    }

    public function edit($id)
    {
        $newStore    = $this->repository->findOrFail($id);
        $accessRight = $this->service->checkLocalityPermission($newStore);
        if (!$accessRight) return redirect()->route($this->service->urlRedirect());
        $formOptions           = $this->service->formOptions($newStore);
        $formOptions['action'] = route('admin.new-stores.update', $id);
        $default_values        = $formOptions['default_values'];
        $new_store_id          = $id;
        $provinces             = $formOptions['provinces'];
        $districts             = $formOptions['districts'] ?? [];
        $wards                 = $formOptions['wards'] ?? [];

        return view('pages.new_stores.edit', compact(
            'formOptions',
            'default_values',
            'new_store_id',
            'provinces',
            'districts',
            'wards',
        ));
    }

    public function update(CreateEditStoreRequest $request, $id)
    {
        $attributes = $request->all();
        $this->service->update($id, $attributes);
        Helper::successMessage('Cập nhật nhà thuốc mới thành công.');
        return redirect()->route('admin.new-stores.index');
    }

    public function destroy($id)
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'message' => 'Đại lý đã được xóa thành công!'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function notApprove(Request $request)
    {
        $newStoreId = $request->get('newStoreId');
        $reason     = $request->get('reason');
        try {
            $result = $this->repository->notApprove($newStoreId, $reason);
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
