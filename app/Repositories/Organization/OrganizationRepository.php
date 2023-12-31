<?php

namespace App\Repositories\Organization;

use App\Helpers\Helper;
use App\Models\District;
use App\Models\Organization;
use App\Models\Province;
use App\Repositories\BaseRepository;
use DB;

class OrganizationRepository extends BaseRepository implements OrganizationRepositoryInterface
{
    /**
     * @return mixed
     */
    public function getModel()
    {
        return new Organization();
    }

    public function paginate(int $limit, array $with = [], array $args = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model
            ->with($with)
            ->when($args['name'] ?? '', function ($query) use ($args) {
                $query->where('name', 'LIKE', '%' . $args['name'] . '%');
            })
            ->orderByRaw('IF(parent_id, CONCAT(parent_id, id), CONCAT(id, id))')
            ->paginate($limit);
    }

    public function updateStatus(array $ids, $status): void
    {
        $this->model->whereIn('id', $ids)->where('status', '!=', $status)
            ->update(['status' => $status]);
    }

    public function formOptions($model = null): array
    {
        $options                                = parent::formOptions($model); // TODO: Change the autogenerated stub
        $options['default_values']['districts'] = $model ? $model->districts->pluck('id')->toArray() : [];

        $options['types']     = Organization::TYPE_TEXTS;
        $options['parents']   = Organization::sortLists();
        $options['provinces'] = Province::query()
            ->orderBy('province_type', 'ASC')
            ->orderBy('province_name', 'ASC')
            ->get();
        $options['districts'] = $model && $model->province_id ? District::query()->where('province_id', $model->province_id)->get() : [];

        return $options;
    }

    public function getDivisionsActive()
    {
        return $this->model->query()->active()->division()->get();
    }

    public function getLocalityActive()
    {
        return $this->model->query()->with('agency')->active()->locality()->get();
    }

    public function getLocalityByDivision($divisionId)
    {
        $currentUser               = Helper::currentUser();
        $organizationOfCurrentUser = Helper::getUserOrganization($currentUser);

        return $this->model->query()
            ->active()
            ->locality()
            ->whereIn('parent_id', (array)$divisionId)
            ->when(isset($organizationOfCurrentUser[Organization::TYPE_DIA_BAN]), function ($q) use ($organizationOfCurrentUser) {
                return $q->whereIn('id', $organizationOfCurrentUser[Organization::TYPE_DIA_BAN]);
            })
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function getLocalityByProvince($province_id)
    {
        $organizationOfCurrentUser = Helper::getUserOrganization();

        return $this->model->query()
            ->active()
            ->where('province_id', $province_id)
            ->where('type', Organization::TYPE_DIA_BAN)
            ->when(isset($organizationOfCurrentUser[Organization::TYPE_DIA_BAN]), function ($q) use ($organizationOfCurrentUser) {
                return $q->whereIn('id', $organizationOfCurrentUser[Organization::TYPE_DIA_BAN]);
            })
            ->get();
    }

    // type : 0 = Tong cty, 1 = cong ty, 2 = Mien, 3 = Khu vuc, 4 = Dia ban
    // Lay danh sach dia ban theo tinh/ thanh pho
    public function getOptionLocality(int $province_id)
    {
        $province_id = $province_id ?? 0;
        $result      = $this->model->where('province_id', $province_id)->where('type', 4)->get();
        return $result;
    }

    public function getUserByLocality(int $organization_id)
    {
        $organization = parent::find($organization_id);
        if ($organization) {
            return $organization->users()->get();
        } else {
            return null;
        }
    }

    public function getOptionLocalityByOrganization(int $organization_id)
    {
        return $this->model->getLocalityByOrganization($organization_id);
    }

    // Lay ra danh sach nhung dia ban theo cay so do
    public function getLocalityByOrganization(int $organization_id)
    {
        $organizationOfCurrentUser = Helper::getUserOrganization();
        $userLocalities            = $organizationOfCurrentUser[Organization::TYPE_DIA_BAN] ?? null;
        $localities                = $this->model->getLocalityByOrganization($organization_id);

        if ($userLocalities && count($localities)) {
            $collection = collect();
            foreach ($localities as $locality) {
                if (in_array($locality->id, $userLocalities)) {
                    $collection->push($locality);
                }
            }

            $localities = $collection;
        }

        return $localities;
    }

    public function getLocalityByArrOrganization(array $organizationId = [])
    {
        $results = collect();
        foreach ($organizationId as $item) {
            if (isset($item)) {
                $tmp = $this->getLocalityByOrganization($item);
                foreach ($tmp as $value) {
                    $results->push($value);
                }
            }
        }

        return $results;
    }

    public function getProvinceByLocalities(array $localities = [])
    {
        return $this->model->whereIn('id', $localities)->distinct()->get('province_id')->toArray();
    }

    public function getChildren($id)
    {
        return $this->model::getChildren($id);
    }


    public function getUserByDivsion(int $organization_id)
    {

        $organization = parent::find($organization_id);
        $locality     = $this->model->where('parent_id', $organization_id)->get();
        $data         = [];
        foreach ($locality as $local) {
            $zz = $local->users()->get();
            foreach ($zz as $z) {
                $data[] = $z->toArray();
            }
        }
        $listTdv = collect($data)->pluck('name', 'id');
//        dd($listTdv);
        return $listTdv;
    }
}
