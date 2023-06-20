<?php

namespace App\Modules\Tdv\Controllers;

use App\Helpers\ApiHelper;
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
use Illuminate\Http\Response;

class PosterStoreRegisterController extends Controller
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
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request, $id)
    {
        $store  = $this->storeRepository->findOrFail($id);
        $search = $request->get('search', []);

        $showOptions   = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "posters.created_at", "type" => "DESC"]]
        ]);
        $allListPoster = $this->service->getListPosters($store, $search, $showOptions);
        $dataPharmacy  = $this->storeService->formOptions($store);
        $formOptions   = $this->service->getOptionListPoster($store);
        $infoPharmacy  = $dataPharmacy['default_values'];
        return view('Tdv::store_posters.show-list', compact(
            'allListPoster',
            'infoPharmacy',
            'formOptions',
            'id'
        ));
    }

    public function list(Request $request)
    {
        $search = $request->get('search', []);

        $showOptions = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "poster_store_registers.created_at", "type" => "DESC"]]
        ]);

        $listStoreUsePoster = $this->service->getListStoreRegister($search, $showOptions);
        $formOptions = $this->service->getOption();
        return view('Tdv::store_posters.list_store_register', compact(
            'listStoreUsePoster',
            'formOptions'
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
        $router = route('admin.tdv.register-store-poster.store', ['store_id' => $store_data->id, 'poster_id' => $poster_data->id,'product_id' => $poster_data->product_id ]);
        $back_link = route('admin.tdv.register-poster.index', $store_data['id']);
        return view('Tdv::store_posters.create', compact(
            'store_data',
            'poster_data',
            'router',
            'back_link'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StorePosterStoreRegisterRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $attributes  = $request->all();
        $user        = auth()->user();
        $poster_area = $attributes['poster_height'] * $attributes['poster_width'] / 10000;
        $item        = array_merge(['tdv_id' => $user->id, 'poster_area' => $poster_area], $attributes);
        try {
            $check_date  = $this->repository->checkDate($item);
            $check_dublicate = $this->repository->checkDublicate($item);
            if ($check_dublicate > 0) {
                Helper::errorMessage('Poster đã được đăng ký với nhà thuốc này.');
                return back();
            }
            if ($check_date > 0) {
                $this->service->create($item);
                Helper::successMessage('Đăng ký chương trình Poster thành công');
                return redirect()->route('admin.tdv.register-poster.list');
            } else {
                Helper::errorMessage('Chương trình đã bị thay đổi, liên hệ với Admin để biết thêm thông tin');
                return back();
            }

        } catch (\Exception $e) {
            Helper::errorMessage('Có lỗi xảy ra, Vui lòng thử lại');
            return back();
        }
    }

    public function image_store($id)
    {
        $store_poster    = $this->repository->find($id);
        $store_poster_id = $store_poster->id;
        $formOptions     = $this->service->formOptions($store_poster);
        $default_values  = $formOptions['default_values'];
        $formOptions['action'] = route('admin.tdv.image-store-poster.post', $store_poster_id);
        return view('Tdv::store_posters.upload-image', compact(
            'formOptions',
            'default_values'
        ));
    }

    public function post_image_store(StorePosterStoreRegisterRequest $request, $id)
    {
        $request->request->add(['type' => PosterStoreRegister::TYPE_IMAGE_POSTER]);
        $attributes = $request->all();
        $this->service->update($attributes, $id);
        Helper::successMessage('Gửi ảnh Poster thành công');
        return redirect()->route('admin.tdv.register-poster.list');
    }

//ANH NT

    public function image_acceptance_store($id)
    {
        $store_poster          = $this->repository->find($id);
        $store_poster_id       = $store_poster->id;
        $formOptions           = $this->service->formOptions($store_poster);
        $default_values        = $formOptions['default_values'];
        $formOptions['action'] = route('admin.tdv.image-acceptance-store.post', $store_poster_id);
        return view('Tdv::store_posters.upload-image', compact(
            'formOptions',
            'default_values'
        ));
    }

    public function post_image_acceptance_store(StorePosterStoreRegisterRequest $request, $id)
    {
        $request->request->add(['type' => PosterStoreRegister::TYPE_IMAGE_ACCEPTANCE]);
        $attributes   = $request->all();
        $store_poster = $this->repository->find($id)->toArray();
        $attributes   = array_merge($store_poster, $attributes);
        try {
//            $this->service->create($attributes);
            $this->service->update($attributes, $id);
            Helper::successMessage('Gửi ảnh Poster thành công');
            return redirect()->route('admin.tdv.register-poster.list');
        } catch (\Exception $e) {
            Helper::errorMessage('Có lỗi xảy ra, Vui lòng thử lại');
            return back();
        }
    }

//OFFER

    public function offer_acceptance($id)
    {
        $store_poster    = $this->repository->find($id);
        $store_poster_id = $store_poster->id;
        $formOptions     = $this->service->formOptions($store_poster);
        $default_values  = $formOptions['default_values'];
        $formOptions['action'] = route('admin.tdv.offer-acceptance.post', $store_poster_id);
        return view('Tdv::store_posters.offer-acceptance', compact(
            'formOptions',
            'default_values'
        ));
    }

    public function post_offer_acceptance(StorePosterStoreRegisterRequest $request, $id)
    {
        $request->request->add(['type' => PosterStoreRegister::TYPE_IMAGE_OFFER]);
        $attributes   = $request->all();
        $store_poster = $this->repository->find($id)->toArray();
        $attributes   = array_merge($store_poster, $attributes);
        $this->service->create($attributes);
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

    public function getPosterByProduct(Request $request)
    {
        try {
            return response()->json([
                'htmlString' => $this->service->getPosterByProduct($request->get('product_id', default: null)),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function showImages($id, $type)
    {
        $poster_reg = PosterStoreRegister::find($id);
        $images = $this->repository->getAllImages($id, $type);
        $info_poster = $images ? $images->first() :'';
        return view('Tdv::store_posters.view_images', compact('images', 'poster_reg', 'info_poster'));
    }
    public function regPoster(Request $request)
    {
        $search = $request->get('search', []);

        $showOptions   = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "posters.created_at", "type" => "DESC"]]
        ]);
        $allListPoster = $this->service->getListPosters('', $search, $showOptions);
        $formOptions   = $this->service->getOptionListPoster('');
        return view('Tdv::store_posters.show-list', compact(
            'allListPoster',
            'formOptions',
        ));
    }
    public function createByPoster($poster_id)
    {
        $poster_data = $this->posterRepository->findOrFail($poster_id);
        $option = $this->service->getListStoreByTdv();
        $router = route('admin.tdv.register-store-poster.store', ['poster_id' => $poster_id, 'product_id' => $poster_data->product_id]);
        $back_link = route('admin.tdv.reg-poster');
        return view('Tdv::store_posters.create', compact(
            'poster_data',
            'router',
            'back_link',
            'option'
        ));
    }
}
