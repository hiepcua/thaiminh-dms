<?php

namespace App\Repositories\LineGroup;

use App\Models\LineGroup;
use App\Repositories\BaseRepository;

class LineGroupRepository extends BaseRepository implements LineGroupRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new LineGroup();
    }

    public function getByLine($lineId = null)
    {
        return $this->model->where('line_id', $lineId)->get();
    }

    public function deleteMultiple(array $arrId = [])
    {
        $this->model->whereIn('id', $arrId)->delete();
    }
}
