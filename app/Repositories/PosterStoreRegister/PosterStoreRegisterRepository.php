<?php

namespace App\Repositories\PosterStoreRegister;

use App\Models\File;
use App\Models\Poster;
use App\Models\PosterStoreRegister;
use App\Models\Product;
use App\Repositories\BaseRepository;
use App\Repositories\Organization\OrganizationRepository;

class PosterStoreRegisterRepository extends BaseRepository implements PosterStoreRegisterRepositoryInterface
{
    protected $organizationRepository;

    public function __construct(
        OrganizationRepository $organizationRepository)
    {
        parent::__construct();
        $this->organizationRepository = $organizationRepository;
    }

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new PosterStoreRegister();
    }

    public function getListPoster($data, $requestParams)
    {
//        dd($data, $requestParams);
        $model      = new $this->model;
        $listPoster = $model::Where('store_id', $data->store_id)
            ->select('poster_store_registers.*')
            ->leftJoin('posters', 'poster_store_registers.poster_id', 'posters.id')
            ->when($requestParams['product'] ?? '', function ($listPoster) use ($requestParams) {
                return $listPoster->where('posters.product_id', 'LIKE', '%' . $requestParams['product'] . '%');
            })
            ->when($requestParams['poster'] ?? '', function ($listPoster) use ($requestParams) {
                return $listPoster->where('poster_store_registers.poster_id', '=', $requestParams['poster']);
            })
            ->groupBy('poster_id')->get();
        $listPoster->map(function ($item, $key) {
            $listImagePoster    = $this->getImages($item);
            $item->image_poster = $listImagePoster[PosterStoreRegister::IMAGE_POSTER];
        });
        $listImages = File::Where('attachment_type', $model::class)->where('attachment_id', $data->id);
        return $listPoster;
    }

    public function getImages($data)
    {
        $model      = new $this->model;
        $showImages = [];
        $listImages = File::Where('attachment_type', $model::class)->where('attachment_id', $data->id);

        $posterImage     = File::Where('attachment_type', $model::class)->where('attachment_id', $data->id)
            ->where('field', PosterStoreRegister::IMAGE_POSTER)
            ->get();
        $acceptanceImage = File::Where('attachment_type', $model::class)
            ->where('attachment_id', $data->id)
            ->where('field', PosterStoreRegister::IMAGE_ACCEPTANCE)
            ->get();
//dd($data);
        $showImages[PosterStoreRegister::IMAGE_POSTER]     = $posterImage->count() ? $posterImage : [];
        $showImages[PosterStoreRegister::IMAGE_ACCEPTANCE] = $acceptanceImage->count() ? $acceptanceImage : [];

        return $showImages;
    }

    public function getByRequest(
        $with = [],
        $requestParams = [],
        $showOption = []
    )
    {
        $query = $this->model
            ->with($with)
            ->leftJoin('stores', 'poster_store_registers.store_id', 'stores.id')
            ->leftJoin('posters', 'poster_store_registers.poster_id', 'posters.id')
            ->when($requestParams['store_code'] ?? '', function ($query) use ($requestParams) {
                return $query->where('stores.name', 'LIKE', '%' . $requestParams['store_code'] . '%');
            })
            ->select('poster_store_registers.*',
                'posters.name as poster_name')
//            ->when($requestParams['product'] ?? '', function ($query) use ($requestParams) {
//                return $query->where('posters.product_id', '=', '%' . $requestParams['product'] . '%');
//            })
            ->groupBy('store_id')->get();
        return $query;
    }

    public function getAllByRequest(
        $with = [],
        $requestParams = [],
        $showOption = []
    )
    {
        $query = $this->getQueryParam(
            $with,
            $requestParams,
        );

        return $this->showOption($query, $showOption);
    }

    public function getQueryParam(
        $with = [],
        $requestParams = [],
    )
    {
//        dd($requestParams);
        $division = isset($requestParams['division_id']) ? $this->organizationRepository->getOptionLocalityByOrganization($requestParams['division_id']) : '';
        $query    = $this->model
            ->with($with)
            ->where('type', '>=', 0)
            ->when($requestParams['pharmacy_name'] ?? '', function ($query) use ($requestParams) {
//                return $query->where('name', 'LIKE', '%' . $requestParams['name'] . '%');
                return $query->whereHas('store', function ($query2) use ($requestParams) {
                    return $query2->where('name', 'LIKE', '%' . $requestParams['pharmacy_name'] . '%')
                        ->orwhere('code', 'LIKE', '%' . $requestParams['pharmacy_name'] . '%');
                });
            })
            ->when(isset($requestParams['poster_id']) && $requestParams['poster_id'] > -1, function ($query) use ($requestParams) {
                return $query->where('poster_id', $requestParams['poster_id']);
            })
            ->when(isset($requestParams['status']) && $requestParams['status'] > -1, function ($query) use ($requestParams) {
                return $query->where('status', $requestParams['status']);
            })
            ->when(isset($requestParams['tdv_id']) && $requestParams['tdv_id'] > -1, function ($query) use ($requestParams) {
                return $query->where('tdv_id', $requestParams['tdv_id']);
            })
            ->when(isset($requestParams['type']) && $requestParams['type'] >= 0, function ($query) use ($requestParams) {
                return $query->where('type', $requestParams['type']);
            })
            ->when($requestParams['division_id'] ?? '', function ($query) use ($division, $requestParams) {
//                return $query->where('name', 'LIKE', '%' . $requestParams['name'] . '%');
                return $query->whereHas('poster', function ($query3) use ($division, $requestParams) {

                    return $query3->whereHas('organizations', function ($query4) use ($division, $requestParams) {
                        return $query4->whereIn('organization_id', $division->pluck('id')->toArray());
                    });
                });
            });

        return $query;
    }

    public function getRegistedById($poster_id, $store_poster)
    {
        $query = $this->model->where('poster_id', $poster_id)->where('store_id', $store_poster)->get();
        return $query;
    }

    public function getNameProduct($product_id)
    {
        $query = Product::find($product_id);
        return $query;
    }


    public function getDataExport(
        $with = [],
        $requestParams = [],
        $showOption = []
    )
    {
        $query = $this->getQueryParam($with, $requestParams);
        foreach ($showOption['orderBy'] ?? [] as $orderBy) {
            if (isset($orderBy['column'])) {
                $query->orderBy($orderBy['column'], $orderBy['type'] ?? 'DESC');
            }
        }
        return $query;
    }

    public function checkDate($item)
    {
        $date  = date('Y/m/d');
        $check = Poster::where('status', 1)
            ->where('id', $item['poster_id'])
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('product_id', $item['product_id'])
            ->count();
        return $check;
    }

    public function checkDublicate($item)
    {
        $check = $this->model::where('poster_id', $item['poster_id'])
            ->where('store_id', $item['store_id'])
            ->count();
        return $check;
    }

    public function getAllImages($id, $type)
    {
        $listImages = File::Where('attachment_type', $this->model::class)
            ->where('attachment_id', $id)
            ->where('field', $type)
            ->get();
        return $listImages;
    }

}
