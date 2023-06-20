<?php

namespace App\Repositories\SystemVariable;

use App\Models\SystemVariable;
use App\Repositories\BaseRepository;

class SystemVariableRepository extends BaseRepository implements SystemVariableRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new SystemVariable();
    }

    public function findByName($name)
    {
        return $this->model->where('name', $name)->first();
    }
}
