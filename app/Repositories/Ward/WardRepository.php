<?php

namespace App\Repositories\Ward;

use App\Models\Ward;
use App\Repositories\BaseRepository;

class WardRepository extends BaseRepository implements WardRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new Ward();
    }

    public function getByDistrict($districtId): array
    {
        return $this->model::query()
            ->select('id', 'district_id', 'ward_name_with_type')
            ->where('district_id', $districtId)
            ->orderBy('ward_type')
            ->orderBy('ward_name')
            ->get()
            ->map(function ($item) {
                return [
                    'id'          => $item->id,
                    'district_id' => $item->district_id,
                    'ward_name'   => $item->ward_name_with_type,
                ];
            })
            ->toArray();
    }
}
