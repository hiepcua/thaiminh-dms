<?php

namespace App\Repositories\Rank;

use App\Models\Rank;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;

class RankRepository extends BaseRepository implements RankRepositoryInterface
{
    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new Rank();
    }

    public function paginate(int $limit, array $with = [], array $args = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model
            ->with($with)
            ->when($args['name'] ?? '', function ($query) use ($args) {
                $query->where('name', 'LIKE', '%' . $args['name'] . '%');
            })
            ->when($args['status'] ?? '', function ($query) use ($args) {
                $query->where('status', $args['status']);
            })
            ->paginate($limit);
    }

    public function create(array $attributes = []){
        $user = Auth::id();
        $attributes['created_by'] = $user;
        return parent::create($attributes);
    }

    public function update(int $id, array $attributes = []){
        $user = Auth::id();
        $attributes['updated_by'] = $user;
        return parent::update($id, $attributes);
    }
}
