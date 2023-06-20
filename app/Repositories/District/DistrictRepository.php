<?php

namespace App\Repositories\District;

use App\Models\District;
use App\Repositories\BaseRepository;

class DistrictRepository extends BaseRepository implements DistrictRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new District();
    }

    public function getByProvince($provinceId): array
    {
        return $this->model::query()
            ->select('id', 'province_id', 'district_name_with_type')
            ->where('province_id', $provinceId)
            ->orderByDesc('district_type')
            ->orderBy('district_name')
            ->get()
            ->map(function ($item) {
                return [
                    'id'            => $item->id,
                    'province_id'   => $item->province_id,
                    'district_name' => $item->district_name_with_type,
                ];
            })
            ->toArray();
    }
}
