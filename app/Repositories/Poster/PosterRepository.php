<?php

namespace App\Repositories\Poster;

use App\Helpers\Helper;
use App\Models\File;
use App\Models\Poster;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class PosterRepository extends BaseRepository implements PosterRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new Poster();
    }

    public function getByRequest(
        $with = [],
        $requestParams = [],
        $showOption = []
    )
    {
        $query = $this->getQuery(
            $with,
            $requestParams,
            $showOption);
        return $this->showOption($query, $showOption);
    }

    public function getPosterByPharmacy(
        $with = [],
        $requestParams = [],
        $showOption = [],
        $model = ''
    )
    {
        $currentUser               = Helper::currentUser();
        $organizationOfCurrentUser = Helper::getUserOrganization($currentUser);
//        dd($organizationOfCurrentUser);
//        $organization_store = $model->organization_id;
        $query              = $this->getQuery(
            $with,
            $requestParams,
            $showOption);
        $query              = $query->when($requestParams['poster_name'] ?? '', function ($query) use ($requestParams) {
            return $query->where('name', 'LIKE', '%' . $requestParams['poster_name'] . '%');
        });
        $query              = $query->when($requestParams['product'] ?? '', function ($query) use ($requestParams) {
            return $query->where('product_id', '=', $requestParams['product']);
        });
        $query              = $query->when($requestParams['status'] ?? '', function ($query) use ($requestParams, $model) {
            if ($requestParams['status'] == 1) {
                $query->whereHas('posterRegisted', function (Builder $q) use ($requestParams, $model) {
                    $q->where('store_id', '=', $model->id);
                });
            }
            else {
                $query->WhereDoesntHave('posterRegisted', function (Builder $q) use ($requestParams, $model) {
                    $q->where('store_id', '=', $model->id);
                });
            }
        });
        //code sau
//        $query = $query->when($organization_store ?? '', function ($q1) use ($organization_store) {
//            return $q1->wherehas('organizations', function ($q2) use ($organization_store) {
//                return $q2->whereIn('organization_id', $organization_store);
//            });
//        });
//        dd($query->toSql());

        return $this->showOption($query, $showOption);

    }


    public function getQuery(
        $with = [],
        $requestParams = [],
        $showOption = []
    )
    {
        $query = $this->model
            ->with($with)
            ->when($requestParams['name'] ?? '', function ($query) use ($requestParams) {
                return $query->where('name', 'LIKE', '%' . $requestParams['name'] . '%');
            })
            ->when($requestParams['product_id'] ?? '', function ($query) use ($requestParams) {
                return $query->where('product_id', '=', $requestParams['product_id']);
//            })
//            ->when($requestParams['organization_id'] ?? '', function ($query) use ($requestParams) {
//                return $query->wherehas('organization_id', '=', 11);
            });
        return $query;
    }

    public function getActivePoster($id = null)
    {
        $data = $this->model::where('id', '>', 0)
            ->when($id ?? '', function ($data) use ($id){
                return $data->where('id', $id);
            })
            ->orderBy('name', 'ASC')->get();
        return $data;
    }

    public function getImages($id)
    {
        $model = new $this->model;
        $image = File::Where('attachment_type', Poster::class)->where('attachment_id', $id)->first();

        return $image;
    }

    public function deleteImage($data)
    {
        $image = File::Where('attachment_type', Poster::class)->where('attachment_id', $data->id)->delete();

        return $image;
    }

    public function getPosterByProduct($id)
    {
        return $this->model::where('product_id', $id)
            ->get();
    }

}
