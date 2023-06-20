<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Http\Requests\SaveAgencyInventoryRequest;
use App\Repositories\ReportAgencyInventory\ReportAgencyInventoryRepositoryInterface;
use App\Services\ReportAgencyInventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportAgencyInventoryController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(
        ReportAgencyInventoryRepositoryInterface $repository,
        ReportAgencyInventoryService $service
    ) {
        $this->repository = $repository;
        $this->service    = $service;
    }

    public function index(Request $request)
    {
        $page_title = '3.4 Hàng tồn đại lý';
        $search     = $request->get('search', []);
        $showOptions = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "agencies.name", "type" => "ASC"]]
        ]);
        $formOptions = $this->service->indexFormOptions();
        $agencyInventoryTable = $this->service->getTable($search, $showOptions);

        return view('pages.agency_inventory.index',
            compact('agencyInventoryTable', 'page_title', 'formOptions')
        );
    }

    public function create()
    {
        //
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

    public function saveInventory(SaveAgencyInventoryRequest $request)
    {
        try {
            $result = $this->service->saveInventory($request->all());

            if ($result['result']) {
                return response()->json([
                    'message' => $result['message']
                ], Response::HTTP_OK);
            }

            return response()->json([
                'message' => $result['message']
            ], Response::HTTP_INTERNAL_SERVER_ERROR);

        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function export(Request $request)
    {
        $showOptions    = $request->get('options', [
            "orderBy" => ["column" => "agencies.name", "type" => "ASC"]
        ]);

        return $this->service->export(
            $request->get('hash_id', null),
            $request->get('search', []),
            $showOptions,
            'agency_order_tdv'
        );
    }
}
