<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Repositories\LineStore\LineStoreRepositoryInterface;
use App\Services\LineStoreService;
use Illuminate\Http\Request;

class LineStoreController extends Controller
{
    protected LineStoreRepositoryInterface $repository;
    private LineStoreService $service;

    public function __construct(
        LineStoreRepositoryInterface $repository,
        LineStoreService             $service
    )
    {
        $this->repository = $repository;
        $this->service    = $service;
    }

    public function index(Request $request)
    {
        $indexOptions = $this->service->indexOptions();
        $search       = $request->get('search', []);
        $showOptions  = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "line_stores.created_at", "type" => "DESC"]]
        ]);
        $table        = $this->service->getTable($search, $showOptions);

        return view('pages.line_stores.list-change', compact('table', 'indexOptions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $formOptions           = $this->service->formOptions($this->repository->findOrFail($id));
        $formOptions['action'] = route('admin.line-store-change.update', $id);
        $default_values        = $formOptions['default_values'];

        return view('pages.line_stores.view', compact(
            'formOptions',
            'default_values',
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $formOptions           = $this->service->formOptions($this->repository->findOrFail($id));
        $formOptions['action'] = route('admin.line-store-change.update', $id);
        $default_values        = $formOptions['default_values'];

        return view('pages.line_stores.edit', compact(
            'formOptions',
            'default_values',
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $attributes    = $request->all();
        $this->service->updateApproved($id, $attributes);
        Helper::successMessage('Cập nhật nhà thuốc thay đổi tuyến thành công.');
        return redirect()->route('admin.line-store-change.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
