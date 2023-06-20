<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Helpers\Helper;
use App\Http\Requests\PosterRequest;
use App\Repositories\Poster\PosterRepositoryInterface;
use App\Repositories\PosterAcceptanceDate\PosterAcceptanceDateRepository;
use App\Services\PosterService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PosterController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(
        PosterRepositoryInterface      $repository,
        PosterService                  $service,
        PosterAcceptanceDateRepository $acceptanceDateRepository,
    )
    {
        $this->repository               = $repository;
        $this->service                  = $service;
        $this->acceptanceDateRepository = $acceptanceDateRepository;

        $this->middleware('permission:xem_chuong_trinh_treo_poster')->only('index');
    }

    public function index(Request $request)
    {
        $page_title = 'Poster';
        $search     = $request->get('search', []);
        $results    = $this->repository->paginate(20, [], $search);

        $search      = $request->get('search', []);
        $showOptions = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "posters.created_at", "type" => "DESC"]]
        ]);
        $table       = $this->service->getTable($search, $showOptions);
        $formOptions = $this->service->formOptions();

        return view('pages.posters.index', compact(
            'table',
            'formOptions'
        ));
    }

    public function create()
    {
        $poster_id             = '';
        $formOptions           = $this->service->formOptions();
        $formOptions['action'] = route('admin.posters.store');
        $default_values        = $formOptions['default_values'];
        $product_user          = [];

        return view('pages.posters.create-update', compact(
            'poster_id',
            'formOptions',
            'default_values',
            'product_user'
        ));
    }

    public function store(PosterRequest $request)
    {
        $attributes = $request->all();
        $attributes['name'] = Helper::convertSpecialCharInput($attributes['name']);
        try {

            $this->service->create($attributes);
            Helper::successMessage('Thêm mới chương trình Poster thành công');
            return redirect()->route('admin.posters.index');
        } catch (\Exception $e) {
            Helper::errorMessage('Có lỗi xảy ra, Vui lòng thử lại');
            return back();
        }
    }

    public function show($id)
    {
        $item = $this->repository->find($id);
    }

    public function edit($id)
    {
        $poster                = $this->repository->find($id);
        $poster_id             = $poster->id;
        $formOptions           = $this->service->formOptions($poster);
        $formOptions['action'] = route('admin.posters.update', $poster_id);
        $default_values        = $formOptions['default_values'];

        return view('pages.posters.create-update', compact(
            'poster_id',
            'formOptions',
            'default_values',
        ));
    }

    public function update(PosterRequest $request, $id)
    {
        $attributes = $request->all();
        $attributes['name'] = Helper::convertSpecialCharInput($attributes['name']);
        try {
        $this->service->update($attributes, $id);
        Helper::successMessage('Sửa chương trình Poster thành công');
        return redirect()->route('admin.posters.index');
        } catch (\Exception $e) {
            Helper::errorMessage('Có lỗi xảy ra, Vui lòng thử lại');
            return back();
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->destroy($id);
            $this->repository->delete($id);

            return response()->json([
                'message' => 'Chương trình Poster đã được xóa thành công!',
                'icon'    => 'success',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }
}
