<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Helpers\Helper;
use App\Http\Requests\CreateEditLineRequest;
use App\Repositories\Line\LineRepositoryInterface;
use App\Services\LineService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LineController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(
        LineRepositoryInterface $repository,
        LineService             $service
    )
    {
        $this->repository = $repository;
        $this->service    = $service;

        $this->middleware('permission:xem_tuyen')->only('index');
        $this->middleware('permission:them_tuyen')->only('create', 'store');
        $this->middleware('permission:sua_tuyen')->only('show', 'edit');
        $this->middleware('permission:xoa_tuyen')->only('destroy');
    }

    public function index(Request $request)
    {
        $indexOptions = $this->service->indexOptions();
        $search       = $request->get('search', []);
        $showOptions  = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "lines.created_at", "type" => "DESC"]]
        ]);

        $isAddNew = Helper::userCan('them_tuyen');

        $results = $this->service->getDataForList($search, $showOptions);

        return view('pages.lines.index', compact('results', 'indexOptions', 'isAddNew'));
    }

    public function create()
    {
        $line                  = $this->repository->getModel();
        $formOptions           = $this->service->formOptions($line);
        $formOptions['action'] = route('admin.lines.store');
        $default_values        = $formOptions['default_values'];

        return view('pages.lines.create-or-edit', compact(
            'formOptions',
            'default_values',
        ));
    }

    public function store(CreateEditLineRequest $request)
    {
        $attributes = $request->all();
        $this->service->create($attributes);
        Helper::successMessage('Thêm tuyến thành công.');
        return redirect()->route('admin.lines.index');
    }

    public function show($id)
    {
        $item = $this->repository->find($id);
    }

    public function edit($id)
    {
        $line                  = $this->repository->find($id, ['stores']);
        $formOptions           = $this->service->formOptions($line, $id);
        $formOptions['action'] = route('admin.lines.update', $id);
        $default_values        = $formOptions['default_values'];

        return view('pages.lines.create-or-edit', compact(
            'formOptions',
            'default_values',
        ));
    }

    public function update(CreateEditLineRequest $request, $id)
    {
        $attributes = $request->all();
        $this->service->update($id, $attributes);
        Helper::successMessage('Sửa tuyến thành công.');
        return redirect()->route('admin.lines.index');
    }

    public function destroy($id)
    {
        try {
            $result = $this->service->deleteLine($id);

            if (!$result['result']) {
                response()->json([
                    'message' => $result['message'] ?? ''
                ], $result['status'] ?? Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
                'message' => $result['message'] ?? ''
            ], $result['status'] ?? Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function getByLocality(Request $request)
    {
        try {
            $result = $this->repository->getByLocality($request->get('locality_id', default: null));
            $html   = view('pages.lines.list-line-locality', compact('result'))->render();

            return response()->json([
                'htmlString' => $html,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }
}
