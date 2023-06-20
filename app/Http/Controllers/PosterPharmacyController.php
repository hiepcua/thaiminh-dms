<?php

namespace App\Http\Controllers;

use App\Exports\PosterRegisterExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePosterStoreRegisterRequest;
use App\Http\Requests\UpdatePosterStoreRegisterRequest;
use App\Models\PosterStoreRegister;
use App\Repositories\Poster\PosterRepository;
use App\Repositories\PosterStoreRegister\PosterStoreRegisterRepository;
use App\Repositories\Store\StoreRepository;
use App\Services\PosterStoreRegisterService;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PosterPharmacyController extends Controller
{
    public function __construct(
        StoreRepository               $storeRepository,
        PosterRepository              $posterRepository,
        PosterStoreRegisterRepository $repository,
        PosterStoreRegisterService    $service,
        StoreService                  $storeService
    )
    {
        $this->storeRepository  = $storeRepository;
        $this->repository       = $repository;
        $this->service          = $service;
        $this->posterRepository = $posterRepository;
        $this->storeService     = $storeService;
        $this->middleware('permission:xem_nha_thuoc_treo_poster')->only('index', 'list');
        $this->middleware('can:download_nt_treo_poster')->only('exportPosterPharmacy');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {


        $search  = $request->get('search', []);
        $results = $this->repository->paginate(20, [], $search);

        $search      = $request->get('search', []);
        $showOptions = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => ["column" => "poster_store_registers.created_at", "type" => "DESC"]
        ]);
        $table       = $this->service->getTable($search, $showOptions);
        $formOptions = $this->service->formOptions();
        $permissionExport = request('search') ? 'download_bao_cao_thuong_key_qc' : '';

        return view('pages.posters-agency.index', compact(
            'formOptions',
            'table',
            'permissionExport'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($store_id, $poster_id)
    {
        $store_data  = $this->storeRepository->findOrFail($store_id);
        $poster_data = $this->posterRepository->findOrFail($poster_id);
        return view('Tdv::store_posters.create', compact(
            'store_data',
            'poster_data'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StorePosterStoreRegisterRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePosterStoreRegisterRequest $request)
    {
        $attributes = $request->all();
        $this->service->create($attributes);
        Helper::successMessage('Đăng ký chương trình Poster thành công');
        return redirect()->route('admin.tdv.register-poster.list');
    }

    public function image_store($id)
    {
        $store_poster    = $this->repository->find($id);
        $store_poster_id = $store_poster->id;
        $formOptions     = $this->service->formOptions($store_poster);
        $default_values  = $formOptions['default_values'];
//        dd($formOptions['imagePoster']['image_poster']);
        $formOptions['action'] = route('admin.tdv.image-store-poster.post', $store_poster_id);
        return view('Tdv::store_posters.upload-image', compact(
            'formOptions',
            'default_values'
        ));
    }

    public function post_image_store(StorePosterStoreRegisterRequest $request, $id)
    {
        $attributes = $request->all();
        $this->service->update($attributes, $id);
        Helper::successMessage('Gửi ảnh Poster thành công');
        return redirect()->route('admin.tdv.register-poster.list');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\PosterStoreRegister $posterStoreRegister
     * @return \Illuminate\Http\Response
     */
    public function show(PosterStoreRegister $posterStoreRegister)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\PosterStoreRegister $posterStoreRegister
     * @return \Illuminate\Http\Response
     */
    public function edit(PosterStoreRegister $posterStoreRegister)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdatePosterStoreRegisterRequest $request
     * @param \App\Models\PosterStoreRegister $posterStoreRegister
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePosterStoreRegisterRequest $request, PosterStoreRegister $posterStoreRegister)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\PosterStoreRegister $posterStoreRegister
     * @return \Illuminate\Http\Response
     */
    public function destroy(PosterStoreRegister $posterStoreRegister)
    {
        //
    }


    function exportPosterPharmacy(Request $request)
    {
        return $this->service->export($request->all());

    }
}
