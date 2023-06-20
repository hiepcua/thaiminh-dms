<?php

namespace App\Repositories\PosterAcceptanceDate;

use App\Models\PosterAcceptanceDate;
use App\Models\PosterOrganization;
use App\Repositories\BaseRepository;

class PosterAcceptanceDateRepository extends BaseRepository implements PosterAcceptanceDateRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new PosterAcceptanceDate();
    }

    public function deletePosterOrganization($poster_id)
    {
//        dd($this->model->where('poster_id', $poster_id)->get(), PosterOrganization::where('poster_id', $poster_id)->get());

        PosterOrganization::where('poster_id', $poster_id)->delete();
        $this->model->where('poster_id', $poster_id)->delete();
    }
}
