<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\Agency;
use App\Models\Organization;
use App\Models\ProductGroup;
use App\Repositories\Agency\AgencyRepositoryInterface;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\Province\ProvinceRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService extends BaseService
{

    public function __construct(
        protected UserRepositoryInterface         $repository,
        protected AgencyRepositoryInterface       $agencyRepository,
        protected FileService                     $fileService,
        protected OrganizationRepositoryInterface $organizationRepository,
        protected ProvinceRepositoryInterface     $provinceRepository,
    )
    {
        parent::__construct();
    }

    public function setModel()
    {
        return new User();
    }

    public function create(array $attributes)
    {
        if (isset($attributes['image'])) {
            $attributes['image'] = $this->fileService->saveFile($attributes['image']);
        } else {
            unset($attributes['image']);
        }
        $user = $this->repository->create($attributes);
        if ($user) {
            $this->updateOptional($user, $attributes);
        }
        return $user;
    }

    public function update(int $id, array $attributes)
    {
        if (isset($attributes['image'])) {
            $attributes['image'] = $this->fileService->saveFile($attributes['image']);
        } else {
            unset($attributes['image']);
        }
        $attributes['change_pass'] = $attributes['change_pass'] ?? false;
        if (!empty($attributes['password'])) {
            $attributes['password'] = Hash::make($attributes['password']);
        } else {
            unset($attributes['password']);
        }

        $user = $this->repository->update($id, $attributes);
        if ($user) {
            $this->updateOptional($user, $attributes);
        }
        return $user;
    }

    function updateOptional($user, $attributes)
    {
        $attributes['role_id']        = $attributes['role_id'] ?? '';
        $attributes['roles']          = (array)$attributes['role_id'];
        $attributes['product_groups'] = array_filter($attributes['product_groups'] ?? []);
        $attributes['organizations']  = array_filter($attributes['organizations'] ?? []);
        $attributes['has_parent_id']  = $attributes['has_parent_id'] ?? 0;
        $attributes['parent_id']      = $attributes['has_parent_id'] ? ($attributes['parent_id'] ?? 0) : 0;
        $attributes['has_agency']     = $attributes['has_agency'] ?? 0;
        $attributes['old_agency_id']  = $attributes['old_agency_id'] ?? 0;

        $user->syncRoles($attributes['roles']);
        $user->product_groups()->sync($attributes['product_groups']);
        $user->organizations()->sync($attributes['organizations']);

        if ($attributes['parent_id']) {
            $this->repository->update($attributes['parent_id'], ['parent_id' => $user->id]);
        } else {
            User::query()
                ->where('parent_id', $user->id)
                ->orWhere('id', $user->id)
                ->update(['parent_id' => 0]);
        }
        if (!$attributes['has_agency'] && $attributes['old_agency_id']) {
            $this->agencyRepository->update($attributes['old_agency_id'], ['is_user' => Agency::ISNT_USER]);
        }
    }

    public function formOptions($model = null): array
    {
        $options = parent::formOptions($model);
        unset($options['default_values']['password']);
        $options['default_values']['change_pass']    = old('change_pass') ?: ($model->id ? $model->change_pass : 1);
        $options['default_values']['role_id']        = old('role_id') ?: $model?->roles->first()?->id;
        $options['default_values']['organizations']  = old('organizations') ?: ($model?->organizations->pluck('id')->toArray() ?: []);
        $options['default_values']['product_groups'] = old('product_groups') ?: ($model?->product_groups->pluck('id')->toArray() ?: []);
        $options['default_values']['has_parent_id']  = old('has_parent_id') ?: ($model?->parent_id);
        $options['default_values']['has_agency']     = old('has_agency') ?: ($model?->agency_id);

        $options['organizations']   = Helper::getTreeOrganization(
            currentUser: true,
            activeTypes: [
                Organization::TYPE_KHU_VUC,
                Organization::TYPE_DIA_BAN,
            ],
            hasRelationship: true,
            setup: [
                'multiple'   => true,
                'name'       => 'organizations[]',
                'class'      => '',
                'id'         => 'user-organizations',
                'attributes' => 'aria-describedby="form-organizations-error"',
                'selected'   => $options['default_values']['organizations'] ?? []
            ]
        );
        $options['agencies']        = $options['default_values']['organizations'] ? $this->agencyRepository->getByLocality($options['default_values']['organizations']) : [];
        $options['product_groups']  = ProductGroup::query()->where('parent_id', 0)->get();
        $options['roles']           = Role::all()->map(function ($item) {
            $item->can_choose_organization  = $item->hasPermissionTo('chon_cay_so_do');
            $item->can_choose_product_group = $item->hasPermissionTo('chon_nhom_san_pham');
            $item->can_choose_agency        = $item->hasPermissionTo('lien_ket_dai_ly');
            return $item;
        });
        $options['role_can_choose'] = [
            'organization'  => $options['roles']->where('can_choose_organization', true)->pluck('id')->toArray(),
            'product_group' => $options['roles']->where('can_choose_product_group', true)->pluck('id')->toArray(),
            'agency'        => $options['roles']->where('can_choose_agency', true)->pluck('id')->toArray(),
        ];
        $options['users']           = User::query()->select('id', 'username', 'name')
            ->orderByDesc('status')
            ->orderBy('name')
            ->get();
        $options['status']          = [
            '1' => 'Hoạt động',
            '0' => 'Ngừng hoạt động',
        ];

        return $options;
    }

    public function getUserLocalities()
    {
        $organizationOfCurrentUser = Helper::getUserOrganization();
        $userRoleName              = Helper::userRoleName();
        $allLocalityActive         = $this->organizationRepository->getLocalityActive();

        if ($userRoleName == User::ROLE_Admin) {
            return $allLocalityActive;
        } else {
            $userLocalities         = $organizationOfCurrentUser[Organization::TYPE_DIA_BAN] ?? [];

            return $allLocalityActive->filter(function ($locality) use ($userLocalities) {
                return in_array($locality->id, $userLocalities);
            });
        }
    }

    public function userProvinces(): ?\Illuminate\Support\Collection
    {
        $organizationOfCurrentUser = Helper::getUserOrganization();
        $allProvinces              = $this->provinceRepository->all() ?? null;
        $userLocalities            = $organizationOfCurrentUser[Organization::TYPE_DIA_BAN] ?? [];
        $userProvinces             = $this->organizationRepository->getProvinceByLocalities($userLocalities) ?? [];
        $arrProvince               = [];
        $collectionUserProvince    = collect();

        if (!empty($userProvinces)) {
            foreach ($userProvinces as $item) {
                $arrProvince[] = $item['province_id'] ?? null;
            }

            foreach ($allProvinces as $province) {
                if (in_array($province->id, $arrProvince)) {
                    $collectionUserProvince->push($province);
                }
            }
        } else {
            $collectionUserProvince = $allProvinces;
        }

        return $collectionUserProvince;
    }
}
