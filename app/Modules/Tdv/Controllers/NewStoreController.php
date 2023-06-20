<?php

namespace App\Modules\Tdv\Controllers;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEditStoreRequest;
use App\Repositories\NewStore\NewStoreRepositoryInterface;
use App\Services\NewStoreService;
use Illuminate\Http\Request;

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
        $this->middleware('can:xem_nha_thuoc_moi')->only('index', 'show');
    }

    public function index(Request $request)
    {
        $indexOptions = $this->service->indexOptions();
        $search       = $request->get('search', []);
        $showOptions  = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "new_stores.created_at", "type" => "DESC"]]
        ]);
        $table        = $this->service->getTableForTdv($search, $showOptions);

        return view('Tdv::new_stores.index', compact('table', 'indexOptions'));
    }

    public function show($id)
    {
        $newStore    = $this->repository->findOrFail($id);
        $accessRight = $this->service->checkLocalityPermission($newStore);
        $isCreated   = $this->service->checkTDVCreated($id);
        if (!$accessRight || !$isCreated) return redirect()->route($this->service->urlRedirect());
        $formOptions    = $this->service->formOptions($newStore);
        $default_values = $formOptions['default_values'];
        return view('Tdv::new_stores.view', compact('default_values', 'formOptions'));
    }

    public function edit($id)
    {
        $newStore    = $this->repository->findOrFail($id);
        $accessRight = $this->service->checkLocalityPermission($newStore);
        $isCreated   = $this->service->checkTDVCreated($id);
        if (!$accessRight || !$isCreated) return redirect()->route($this->service->urlRedirect());
        $formOptions           = $this->service->getFormOptionsTDV($newStore);
        $formOptions['action'] = route('admin.tdv.new-stores.update', $id);
        $default_values        = $formOptions['default_values'];
        $new_store_id          = $id;
        $localities            = $formOptions['localities'];
        $provinces             = $formOptions['provinces'];
        $districts             = $formOptions['districts'] ?? [];
        $wards                 = $formOptions['wards'] ?? [];

        return view('Tdv::new_stores.edit', compact(
            'formOptions',
            'default_values',
            'new_store_id',
            'localities',
            'provinces',
            'districts',
            'wards',
        ));
    }

    public function update(CreateEditStoreRequest $request, $id)
    {
        $attributes = $request->all();
        $this->service->updateStoreByTDV($id, $attributes);
        Helper::successMessage('Cập nhật nhà thuốc mới thành công.');
        return redirect()->route('admin.tdv.new-stores.index');
    }
}
