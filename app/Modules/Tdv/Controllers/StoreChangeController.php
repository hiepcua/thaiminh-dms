<?php

namespace App\Modules\Tdv\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEditStoreRequest;
use App\Models\StoreChange;
use App\Repositories\StoreChange\StoreChangeRepositoryInterface;
use App\Services\StoreChangeService;
use Illuminate\Http\Request;
use App\Helpers\Helper;

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
        $this->middleware('can:xem_nha_thuoc_thay_doi')->only('index', 'show');
    }

    public function index(Request $request)
    {
        $indexOptions = $this->service->indexOptions();
        $search       = $request->get('search', []);
        $showOptions  = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "store_changes.created_at", "type" => "DESC"]]
        ]);
        $table        = $this->service->getTableForTdv($search, $showOptions);

        return view('Tdv::store_changes.index', compact('table', 'indexOptions'));
    }

    public function show($id)
    {
        $storeChange = $this->repository->findOrFail($id);
        $accessRight = $this->service->checkLocalityPermission($storeChange);
        $isCreated   = $this->service->checkTDVCreated($id);
        if (!$accessRight || !$isCreated) return redirect()->route($this->service->urlRedirect());
        $formOptions    = $this->service->formOptionsTDV($storeChange);
        $default_values = $formOptions['default_values'];
        $current_store  = $formOptions['current_store'];
        $canEdit        = $default_values['status'] == StoreChange::STATUS_INACTIVE && $default_values['created_by'] == Helper::currentUser()->id;
        return view('Tdv::store_changes.view', compact(
            'formOptions',
            'default_values',
            'current_store',
            'canEdit'
        ));
    }

    public function edit($id)
    {
        $storeChange = $this->repository->findOrFail($id);
        $accessRight = $this->service->checkLocalityPermission($storeChange);
        $isCreated   = $this->service->checkTDVCreated($id);
        if (!$accessRight || !$isCreated) return redirect()->route($this->service->urlRedirect());
        if ($storeChange->status == StoreChange::STATUS_INACTIVE) {
            $formOptions           = $this->service->formOptionsTDV($storeChange);
            $formOptions['action'] = route('admin.tdv.store-changes.update', $id);
            $default_values        = $formOptions['default_values'];
            $new_store_id          = $id;
            $provinces             = $formOptions['provinces'];
            $districts             = $formOptions['districts'] ?? [];
            $wards                 = $formOptions['wards'] ?? [];
            return view('Tdv::store_changes.edit', compact(
                'formOptions',
                'default_values',
                'new_store_id',
                'provinces',
                'districts',
                'wards',
            ));
        } else {
            return redirect()->route('admin.tdv.store-changes.index');
        }
    }

    public function update(CreateEditStoreRequest $request, $id)
    {
        $attributes = $request->all();
        $this->service->updateTDV($id, $attributes);
        Helper::successMessage('Cập nhật nhà thuốc thay đổi thành công.');
        return redirect()->route('admin.tdv.store-changes.index');
    }

}
