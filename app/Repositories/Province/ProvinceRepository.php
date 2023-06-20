<?php

namespace App\Repositories\Province;

use App\Models\Province;
use App\Repositories\BaseRepository;

class ProvinceRepository extends BaseRepository implements ProvinceRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new Province();
    }

    public function getByCode($code)
    {
        return $this->model->where('province_code', $code)->first();
    }
}
