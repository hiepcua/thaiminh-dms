<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\Agency;
use App\Models\Organization;
use App\Models\Store;
use App\Models\User;
use App\Repositories\Agency\AgencyRepositoryInterface;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\Province\ProvinceRepositoryInterface;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrganizationService extends BaseService
{
    protected $repository;
    protected $agencyRepository;
    protected $storeRepository;
    protected $userRepository;
    protected $provinceRepository;

    public function __construct(
        OrganizationRepositoryInterface $repository,
        AgencyRepositoryInterface       $agencyRepository,
        StoreRepositoryInterface        $storeRepository,
        UserRepositoryInterface         $userRepository,
        ProvinceRepositoryInterface     $provinceRepository
    )
    {
        parent::__construct();

        $this->repository         = $repository;
        $this->agencyRepository   = $agencyRepository;
        $this->storeRepository    = $storeRepository;
        $this->userRepository     = $userRepository;
        $this->provinceRepository = $provinceRepository;
    }

    public function setModel()
    {
        return new Organization();
    }

    public function dataLists(Request $request): \Illuminate\Support\Collection
    {
        $search = $request->get('search');
        if (empty($search['name']) && empty($search['type'])) {
            $results = Organization::sortLists();
        } else {
            $results = Organization::query()
                ->with(['users', 'districts', 'province'])
                ->when($search['name'] ?? '', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search['name'] . '%');
                })
                ->when($search['type'] ?? '', function ($query) use ($search) {
                    $query->where('type', '=', $search['type']);
                })
                ->orderBy('name', 'ASC')
                ->get();
        }

        return $results;
    }

    public function create($attributes)
    {
        $organization = $this->repository->create($attributes);
        if ($organization) {
            $this->updateDistricts($organization, $attributes);
        }
    }

    public function update($id, $attributes)
    {
        $organization = $this->repository->update($id, $attributes);
        if ($organization) {
            $this->updateDistricts($organization, $attributes);

            if ($organization->status == Organization::STATUS_INACTIVE) {
                $children = $this->getAllChildren($organization->id);
                if ($children->isNotEmpty()) {
                    $this->repository->updateStatus($children->pluck('id')->toArray(), Organization::STATUS_INACTIVE);
                }
            }
        }
    }

    function updateDistricts($organization, array $attributes): void
    {
        $attributes['districts'] = array_filter($attributes['districts'] ?? []);
        $organization->districts()->sync($attributes['districts']);
    }

    function checkDelete($id): array
    {
        $organization = $this->repository->findOrFail($id);
        $children     = $this->getAllChildren($id);
        $ids          = $children->add($organization)->pluck('id')->toArray();
        $output       = ['message' => '', 'deleted' => false];
        $has_agency   = $this->agencyRepository->organizationExists($ids);
        if ($has_agency) {
            $output['message'] = $organization->name . ' đang đươc sử dụng bởi Đại lý.';
            return $output;
        }
        $has_store = $this->storeRepository->organizationExists($ids);
        if ($has_store) {
            $output['message'] = $organization->name . ' đang đươc sử dụng bởi Nhà thuốc.';
            return $output;
        }
        $has_user = $this->userRepository->organizationExists($ids);
        if ($has_user) {
            $output['message'] = $organization->name . ' đang đươc sử dụng bởi Người dùng.';
            return $output;
        }
        $output['deleted'] = true;
        return $output;
    }

    function getAllChildren(int $parent_id = 0): \Illuminate\Support\Collection
    {
        return $this->model->sortLists($parent_id);
    }

    public function getParent($parent_id)
    {
        return $this->repository->findOrFail($parent_id);
    }

    public function getOptionLocalityByDivision($divisionId)
    {
        $localities = $this->repository->getLocalityByDivision($divisionId);

        $htmlString = "<option value='' class='ajax-locality-option'>- Địa bàn -</option>";

        foreach ($localities as $locality) {
            $htmlString .= "<option value='$locality->id' data-parent='$locality->parent_id' class='ajax-locality-option'>$locality->name</option>";
        }

        return $htmlString;
    }

    public function getOptionUserByLocality($locality_id)
    {
        $localityIds = $locality_id ? $this->repository->getChildren($locality_id)->pluck('id')->toArray() : [];
        $users       = $locality_id ? $this->userRepository->getByOrganizations($localityIds, 'TDV') : $this->userRepository->getByRole('TDV');
        $htmlString  = '';

        foreach ($users as $user) {
            $htmlString .= sprintf('<option value="%s" class="ajax-tdv-option">%s</option>', $user->id, $user->name);
        }

        return $htmlString;
    }

    public function getOptionLocalityByProvince($provinceId)
    {
        $localities = $this->repository->getLocalityByProvince($provinceId);

        $htmlString = '';

        if ($localities->isEmpty()) {
            $htmlString .= "<option value='' class='ajax-locality-option'>Không có dữ liệu</option>";
        } else {
            $htmlString .= "<option value='' class='ajax-locality-option'>-- Chọn một --</option>";
            foreach ($localities as $locality) {
                $htmlString .= "<option value='$locality->id' class='ajax-locality-option'>$locality->name</option>";
            }
        }
        return $htmlString;
    }

    public function getUserByLocality($locality_id)
    {
        $users = $this->userRepository->getByOrganizations([$locality_id], 'TDV', 'avatar');

        $htmlString = '';

        if ($users->isEmpty()) {
            $htmlString .= '<div class="item-tdv">Chưa có trình dược viên</div>';
        } else {
            foreach ($users as $user) {
                $htmlString .= sprintf('<div class="item-tdv d-flex align-items-center">%s%s</div>',
                    ($user->avatar ? sprintf('<div class="avatar me-50"><img src="%s" alt="Avatar" width="38" height="38"/></div>',
                        Storage::url($user->avatar->source)) : ''),
                    ($user->name . ' - ' . $user->phone)
                );
            }
        }
        return $htmlString;
    }

    public function getProvinceByDivision($divisionId): array
    {
        $provinces     = [];
        $firstLocality = $this->repository->getLocalityByDivision($divisionId)
            ->map(function ($item) {
                $item->name_ascii = Str::ascii($item->name);
                return $item;
            })
            ->sortBy('name_ascii')
            ->first();
        $firstLocality->load('districts');
//        dd($firstLocality,$firstLocality->districts);
        $firstLocality->districts->each(function ($item) use (&$provinces) {
            $provinces[$item->province_id][] = $item->district_code;
        });
        if (!$provinces) {
            $provinces[$firstLocality->province_id] = [];
        }

        return $provinces;
    }

    public function getProvinceByLocalities($localities)
    {
        $provinceId = array_unique($this->repository->getByArrId($localities)->pluck('province_id')->toArray());

        return $this->provinceRepository->getByArrId($provinceId)->pluck('province_name_with_type', 'province_code')->toArray();
    }

    public function getDisctricByLocalities($localities)
    {
        $result        = [];
        $organizations = $this->repository->getByArrId($localities);

        foreach ($organizations as $organization) {
            if ($organization->districts) {
                foreach ($organization->districts as $district) {
                    $result[$district->district_code] = $district->district_name_with_type;
                }
            }
        }

        return $result;
    }


    public function getUserByDivision($division_id = null)
    {

        $data = '<option value="-1" class="ajax-tdv-option">--chọn TDV--</option>';
        if ($division_id) {
            $users = $this->repository->getUserByDivsion($division_id);
            if ($users->isEmpty()) {
                $data .= '<div class="item-tdv">Chưa có trình dược viên</div>';
            } else {
                foreach ($users as $key => $user) {
                    $data .= '<option value="' . $key . '" class="ajax-tdv-option">' . $user . '</option>';
                }
            }
        }
        return $data;
    }
}
