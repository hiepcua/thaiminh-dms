<?php

namespace App\Repositories\Gift;

use App\Models\Gift;
use App\Repositories\BaseRepository;

class GiftRepository extends BaseRepository implements GiftRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new Gift();
    }

    public function getByRequest($paginate, $with = [], $requestParams = [])
    {
        return $this->model
            ->with($with)
            ->when(isset($requestParams['codeOrName']), function ($query) use ($requestParams) {
                return $query->where('code', $requestParams['codeOrName'])
                    ->orWhere('name', 'like', "%" . $requestParams['codeOrName'] . "%");
            })
            ->orderBy('updated_at', 'DESC')
            ->paginate($paginate);
    }
}
