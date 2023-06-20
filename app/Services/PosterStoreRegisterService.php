<?php

namespace App\Services;

use App\Exports\PosterRegisterExport;
use App\Helpers\Helper;
use App\Helpers\SearchFormHelper;
use App\Helpers\TableHelper;
use App\Models\AgencyOrder;
use App\Models\File;
use App\Models\Organization;
use App\Models\Product;
use App\Repositories\Organization\OrganizationRepository;
use App\Repositories\Poster\PosterRepository;
use App\Repositories\PosterStoreRegister\PosterStoreRegisterRepository;
use App\Services\BaseService;
use App\Models\PosterStoreRegister;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PosterStoreRegisterService extends BaseService
{
    protected $repository;

    public function __construct(
        PosterStoreRegisterRepository $repository,
        PosterRepository              $posterRepository,
        OrganizationRepository        $organizationRepository
    )
    {
        parent::__construct();
        $this->repository             = $repository;
        $this->posterRepository       = $posterRepository;
        $this->organizationRepository = $organizationRepository;
    }

    public function setModel()
    {
        return new PosterStoreRegister();
    }

    public function getTable($search = [], $showOptions = [])
    {
        $listPharmacy  = $this->repository->getAllByRequest(
            with: [],
            requestParams: $search,
            showOption: $showOptions
        );
        $currentUser   = Helper::currentUser();
        $canEditPoster = $currentUser->can('xem_nha_thuoc_treo_poster');
        $cur_page      = $listPharmacy->currentPage();
        $per_page      = $listPharmacy->perPage();

        $listPharmacy->map(function ($item, $key) use ($canEditPoster, $cur_page, $per_page) {
            $item->stt           = ($key + 1) + ($cur_page - 1) * $per_page;
            $item->pharmacy_address = $item->store->address ?? '';
            $item->pharmacy_phone = $item->store->phone_web ?? '';
            $item->pharmacy_name = $item->store->name .'-'. $item->store->code .'<br> ĐC: '.$item->pharmacy_address .'<br> SĐT: '.$item->pharmacy_phone?? '';
            $item->poster_name   = $item->poster->name ?? '';
            $item->tdv_name      = $item->tdv->name;
            $item->reward        = '';
            $status              = PosterStoreRegister::STATUS[$item->status];
            $type                = PosterStoreRegister::TYPE[$item->type];
            $item->status        = $type . ' - ' . $status;
            $listImagePoster     = $this->repository->getImages($item);
//            dd($listImagePoster);
            $image = '';
            foreach ($listImagePoster as $listImage) {
                if (count($listImage)) {
                    foreach ($listImage as $data) {
                        $path  = str_replace('public', 'storage', $data->source);
                        $image .= '<img style="max-width:100px;" src="' . $path . '">';
                    }
                }
//                dd($image);
            }
//            dd($image);
            $item->list_images = $image;

            return $item;
        });

        return new TableHelper(
            collections: $listPharmacy,
            nameTable: 'poster-pharmacy',
        );

    }

    public function formOptions($model = null): array
    {
        $options                     = parent::formOptions($model); // TODO: Change the autogenerated stub
        $options['image_poster']     = PosterStoreRegister::IMAGE_POSTER;
        $options['image_acceptance'] = PosterStoreRegister::IMAGE_ACCEPTANCE;
        if (isset($model)) {
            $options['store']       = $model->store()->first();
            $options['poster']      = $model->poster()->first();
            $options['imagePoster'] = $this->repository->getImages($model);
        }
//        dd($options);


        $options['status']      = [-1 => '--Trạng thái--'] + PosterStoreRegister::STATUS;
        $options['types']       = [-1 => '--Loại--'] + PosterStoreRegister::TYPE;

        $options['listTdv'] = [-1 => '--TDV--'];
        $listPosters            = $this->posterRepository->getActivePoster()->pluck('name', 'id')->toArray();
        $options['listPosters'] = ([-1 => '--Poster--'] + $listPosters);


//dd($listPosters, $options['listPosters']);
//        $options['root_products'] = Product::query()->where('parent_id', 0)->get();
        $options['month'] = [
            '1' => '1 Tháng',
            '2' => '2 Tháng',
            '3' => '3 Tháng',
            '4' => '4 Tháng',
        ];
        return $options;
    }

    public function getListPosters($model = null, $requestParams, $showOptions): array
    {
        $listPoster = $this->posterRepository->getPosterByPharmacy(
            [],
            $requestParams,
            $showOptions,
            $model,
        );
        $date       = date('Y-m-d h:i:s');
        $listPoster->map(function ($item) use ($model, $date) {
//            dd($date, $date >= $item->start_date && $date <= $item->end_date, $item);
            if ($date >= $item->start_date && $date <= $item->end_date) {
                $item->active = 1;
            } else {
                $item->active = 2;
            }
            $item->product_name = $this->repository->getNameProduct($item->product_id)->name;
            $item->register     = 0;
            if ($model) {
                $check = $this->repository->getRegistedByID($item->id, $model->id);
                if (count($check)) {
                    $item->register = count($check);
                }
            }
        }
        );
        $data = $listPoster->getCollection()->sortByDesc('created_at')->sortBy(['active', 'register'])->values();

        return $data->toArray();
    }

    public function getOption()
    {
        $options['root_products'] = Product::query()->where('parent_id', 0)->pluck('name', 'id')->toArray();
        $searchOptions            = [];
        $searchOptions[]          = [
            'type'         => 'text',
            'name'         => 'search[store_code]',
            'placeholder'  => 'Mã nhà thuốc',
            'defaultValue' => request('search.store_code') ? request('search.store_code') : '',
        ];
        $searchOptions[]          = [
            'type'         => 'select2',
            'name'         => 'search[product]',
            'class'        => 'col-md-2',
            'id'           => 'select_product',
            'options'      => ['' => '--Chọn sản phẩm--'] + $options['root_products'],
            'defaultValue' => request('search.product') ? request('search.product') : '',
        ];

        $posterOption    = request('search.product') ? $this->posterRepository->getPosterByProduct(request('search.product'))->pluck('name', 'id')->toArray() : [];
        $searchOptions[] = [
            'type'          => 'select2',
            'name'          => 'search[poster]',
            'class'         => 'col-md-2',
            'id'            => 'select_poster',
            'options'       => ['' => '--Poster--'] + $posterOption,
            'other_options' => ['option_class' => 'ajax-poster-product'],
            'defaultValue'  => request('search.poster') ? request('search.poster') : '',
        ];
        return compact('searchOptions');

    }

    public function getOptionListPoster($store)
    {
        $options['root_products'] = Product::query()->where('parent_id', 0)->pluck('name', 'id')->toArray();
        $searchOptions            = [];
        $searchOptions[]          = [
            'type'         => 'text',
            'name'         => 'search[poster_name]',
            'placeholder'  => 'Tên chương trình',
            'defaultValue' => request('search.poster_name') ? request('search.poster_name') : '',
        ];
        $searchOptions[]          = [
            'type'         => 'select2',
            'name'         => 'search[product]',
            'class'        => 'col-md-2',
            'id'           => 'select_product',
            'options'      => array_merge([0 => '--Chọn sản phẩm--'], $options['root_products']),
            'defaultValue' => request('search.product') ? request('search.product') : '',
        ];
        $searchOptions[]          = [
            'type'         => 'selection',
            'name'         => 'search[status]',
            'options'      => [
                '' => '--Trạng thái--',
                0  => 'Chưa đăng ký',
                1  => 'Đã đăng ký'
            ],
            'defaultValue' => request('search.status') ? request('search.status') : '',
        ];
        if ($store) {
            $route = route('admin.tdv.register-poster.index', $store->id);
        } else {
            $route = route('admin.tdv.reg-poster');
        }

        $searchForm = SearchFormHelper::getForm(
            route: $route,
            method: 'GET',
            config: $searchOptions,
        );

        return compact('searchForm');

    }


    public function getPosterByProduct($id)
    {
        $data    = '<option value="-1" class="ajax-tdv-option">--Chọn poster--</option>';
        $posters = $this->posterRepository->getPosterByProduct($id)->pluck('name', 'id')->toArray();
        if (count($posters) == 0) {
            $data .= '<div>Chưa có poster</div>';
        } else {
            foreach ($posters as $key => $poster) {
                $data .= '<option value="' . $key . '" class="ajax-poster">' . $poster . '</option>';
            }
        }
        return $data;
    }

    public function getListStoreRegister($requestParams, $showOptions): array
    {
        $listStore = $this->repository->getByRequest(
            ['store'],
            $requestParams,
            $showOptions,
        );
        $listStore->map(function ($item, $key) use ($requestParams) {
            $item->store_address = $item->store->address . ' - ' . $item->store->district->district_name . ' - ' . $item->store->province->province_name ?? '';
            $item->listPoster    = $this->repository->getListPoster($item, $requestParams);
//            dd($item);
            $item->listPoster->map(function ($image) {
                $actions = [
                    ['text' => 'Xem ảnh treo', 'router' => route('admin.tdv.show-images', [$image->id, PosterStoreRegister::IMAGE_POSTER])],
                    ['text' => 'Xem ảnh NT', 'router' => route('admin.tdv.show-images', [$image->id, PosterStoreRegister::IMAGE_ACCEPTANCE])],
                ];

                $image->actions = $actions;
            });

        });
        return $listStore->toArray();
    }

    public function create($attributes)
    {
        $created                = $this->repository->create($attributes);
        $uploadImages           = isset($attributes[PosterStoreRegister::IMAGE_POSTER]) ? $this->uploadImages($attributes[PosterStoreRegister::IMAGE_POSTER], PosterStoreRegister::IMAGE_POSTER, $created->id) : '';
        $uploadImagesAcceptance = isset($attributes[PosterStoreRegister::IMAGE_ACCEPTANCE]) ? $this->uploadImages($attributes[PosterStoreRegister::IMAGE_ACCEPTANCE], PosterStoreRegister::IMAGE_ACCEPTANCE, $created->id) : '';
    }

    /**
     * @return mixed
     */
    public function update($attributes, $id)
    {
        $uploadImages           = isset($attributes[PosterStoreRegister::IMAGE_POSTER]) ? $this->uploadImages($attributes[PosterStoreRegister::IMAGE_POSTER], PosterStoreRegister::IMAGE_POSTER, $id) : '';
        $uploadImagesAcceptance = isset($attributes[PosterStoreRegister::IMAGE_ACCEPTANCE]) ? $this->uploadImages($attributes[PosterStoreRegister::IMAGE_ACCEPTANCE], PosterStoreRegister::IMAGE_ACCEPTANCE, $id) : '';

        $this->repository->update($id, $attributes);
    }


    public function uploadImages($images, $field, $id): ?array
    {
        if (!empty($images)) {
            foreach ($images as $image) {
                $mime_type = $image->getMimeType();
                $file_name = $image->getClientOriginalName();
                $path      = Storage::putFile('public/images/' . $field, $image);
                $file      = File::create([
                    'mime_type'       => $mime_type,
                    'name'            => $file_name,
                    'disk_name'       => pathinfo($path)['basename'],
                    'source'          => $path,
                    'created_by'      => Auth::id(),
                    'attachment_id'   => $id,
                    'attachment_type' => PosterStoreRegister::class,
                    'field'           => $field,
                ]);
            }
        }
        return $fileIds ?? null;
    }

    public function summaryExportOptions(): array
    {
        $filename = 'export_poster_pharmacy' . '_' . \Illuminate\Support\Carbon::now()->timestamp . ".xlsx";
        return [
            'hash_id'        => request('hash_id', ''),
            'file_name'      => $filename,
            'file_dir'       => 'agency_order',
            'route_download' => route('admin.file.action', [
                'type'     => 'download',
                'folder'   => 'agency_order',
                'nameFile' => $filename,
            ]),
            'header_multi'   => true,
        ];
    }

    public function export($requestParams)
    {
        $showOptions = [
            "perPage" => config("table.default_paginate"),
            "orderBy" => ["column" => "poster_store_registers.created_at", "type" => "DESC"]
        ];

        $rows = $this->repository->getAllByRequest(
            with: [],
            requestParams: $requestParams['search'],
            showOption: $showOptions
        );
        $rows->map(function ($item, $key) {
            $item->pharmacy_name    = $item->store->name .'-'.$item->store->code ?? '';
            $item->poster_name      = $item->poster->name ?? '';
            $item->pharmacy_address = $item->store->address;
            $item->pharmacy_phone = $item->store->phone_web;
            $item->reward           = '';
            $status                 = PosterStoreRegister::STATUS[$item->status];
            $type                   = PosterStoreRegister::TYPE[$item->type];
            $item->status           = $type . ' - ' . $status;

            return $item;
        });

        list('file_name' => $filename, 'file_dir' => $fileDir, 'route_download' => $routeDownload,) = $this->summaryExportOptions();

        $title = 'Danh sách ';
        if ($requestParams['search']['type'] > 0) {
            $title .= PosterStoreRegister::TYPE[$requestParams['search']['type']];
        }
        if ($requestParams['search']['poster_id'] > 0) {
            $poster_name = $this->posterRepository->getActivePoster($requestParams['search']['poster_id']) ?? '';
            $title .= $this->posterRepository->getActivePoster($requestParams['search']['poster_id'])[0]->name;
        }
        Excel::store(new PosterRegisterExport($title, $rows), $fileDir . '/' . $filename);
        return [
            'done'          => true,
            'total'         => $rows->count(),
            'processed'     => $rows->count(),
            'percent'       => 100,
            'file_name'     => $filename,
            'current_step'  => 1,
            'started_at'    => now()->format('Y-m-d H:i:s'),
            'download'      => $routeDownload,
            'progress_info' => sprintf('<span>File:</span><span><a class="text-success" href="%s">%s</a></span>', $routeDownload, $filename),
        ];
    }

    public function getListStoreByTdv()
    {
        $current_user = Helper::currentUser();
        $data         = $this->getDataByLocality($current_user->organizations->pluck('id')->toArray());
        $store        = $data['stores'];
        return compact('store');
    }

    public function getDataByLocality($localityId)
    {
        $divisionId = [];
        if (!$localityId && $userOrganizations = Helper::getUserOrganization()) {
            if (count($userOrganizations[Organization::TYPE_DIA_BAN]) > 1) {
                $localityId = $userOrganizations[Organization::TYPE_DIA_BAN];
            } else {
                $localityId = array_key_first($userOrganizations[Organization::TYPE_DIA_BAN]);
            }
            $divisionId = $userOrganizations[Organization::TYPE_KHU_VUC];
        }
        if (is_array($localityId)) {
            $organizations = $this->organizationRepository->getByArrId($localityId, ['agency', 'stores']);
            $agencies      = collect([]);
            $stores        = collect([]);
            foreach ($organizations as $organization) {
                $agencies = $agencies->merge($organization->agency)->unique('id');
                $stores   = $stores->merge($organization->stores)->unique('id');
            }
        } else {
            $organization = $this->organizationRepository->find($localityId, ['agency', 'stores']);
            $divisionId   = [$organization->parent_id];

        }
        $messages = [];
        if ($stores->isEmpty()) {
            $messages[] = 'Không tìm thấy nhà thuốc';
        }
        return compact('stores', 'messages');
    }

}