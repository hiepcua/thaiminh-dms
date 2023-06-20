<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Helpers\TableHelper;
use App\Models\LineStore;
use App\Models\File;
use App\Models\Organization;
use App\Models\Store;
use App\Models\User;
use App\Repositories\District\DistrictRepositoryInterface;
use App\Repositories\File\FileRepositoryInterface;
use App\Repositories\Line\LineRepositoryInterface;
use App\Repositories\LineStore\LineStoreRepositoryInterface;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\NewStore\NewStoreRepositoryInterface;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Repositories\Province\ProvinceRepositoryInterface;
use App\Models\NewStore;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class NewStoreService extends BaseService
{
    protected NewStoreRepositoryInterface $repository;
    protected StoreRepositoryInterface $storeRepository;
    protected StoreService $storeService;
    protected OrganizationRepositoryInterface $organizationRepository;
    protected LineStoreService $lineStoreService;
    protected LineStoreRepositoryInterface $lineStoreRepository;
    protected ProvinceRepositoryInterface $provinceRepository;
    protected DistrictRepositoryInterface $districtRepository;
    protected FileRepositoryInterface $fileRepository;
    protected LineRepositoryInterface $lineRepository;
    protected UserService $userService;

    public function __construct(
        NewStoreRepositoryInterface     $repository,
        StoreRepositoryInterface        $storeRepository,
        StoreService                    $storeService,
        OrganizationRepositoryInterface $organizationRepository,
        LineStoreService                $lineStoreService,
        LineStoreRepositoryInterface    $lineStoreRepository,
        ProvinceRepositoryInterface     $provinceRepository,
        DistrictRepositoryInterface     $districtRepository,
        FileRepositoryInterface         $fileRepository,
        LineRepositoryInterface         $lineRepository,
        UserService                     $userService,
    )
    {
        parent::__construct();
        $this->repository             = $repository;
        $this->storeRepository        = $storeRepository;
        $this->storeService           = $storeService;
        $this->organizationRepository = $organizationRepository;
        $this->lineStoreService       = $lineStoreService;
        $this->lineStoreRepository    = $lineStoreRepository;
        $this->provinceRepository     = $provinceRepository;
        $this->districtRepository     = $districtRepository;
        $this->fileRepository         = $fileRepository;
        $this->userService            = $userService;
        $this->lineRepository         = $lineRepository;
    }

    public function setModel()
    {
        return new NewStore();
    }

    public function formOptions($model = null): array
    {
        return $this->getFormOptions($model);
    }

    public function getFormOptionsTDV($model): array
    {
        $options               = $this->getFormOptions($model);
        $options['localities'] = $this->organizationRepository->getLocalityActive();

        return $options;
    }

    public function getFormOptions($model = null): array
    {
        $options                   = parent::formOptions($model);
        $options['status']         = NewStore::STATUS_TEXTS;
        $options['storeType']      = Store::STORE_TYPE;
        $options['provinces']      = $this->provinceRepository->all() ?? null;
        $options['userLocalities'] = $this->userService->getUserLocalities(); // Tat ca dia ban user duoc phep
        $options['userProvinces']  = $this->userService->userProvinces(); // Tat ca tinh/tp user duoc phep
        $options['currentStatus']  = $options['default_values']['status'];
        $currentRoute              = request()->route()->getName();
        $default_values            = $options['default_values'];
        $locality_id               = $default_values['organization_id'] ?? 0;

        if (
            $currentRoute == 'admin.new-stores.edit' ||
            $currentRoute == 'admin.new-stores.show' ||
            $currentRoute == 'admin.new-stores.approve' ||
            $currentRoute == 'admin.tdv.new-stores.edit' ||
            $currentRoute == 'admin.tdv.new-stores.show'
        ) {
            $newStoreId                  = $model->id;
            $options['province']         = $model->province;
            $options['district']         = $model->district;
            $options['ward']             = $model->ward;
            $options['user']             = $model->user;
            $options['userUpdate']       = $model->userUpdate;
            $options['tdv']              = $this->organizationRepository->getUserByLocality($locality_id) ?? collect();
            $options['lineStore']        = $model->load('lineStore')->lineStore ?? null;
            $options['line']             = isset($options['lineStore']) ? $options['lineStore']?->load('line')?->line : null;
            $arrFileId                   = $default_values['file_id'] ? json_decode($default_values['file_id'], true) : [];
            $options['files']            = $this->fileRepository->getByArrId($arrFileId);
            $options['parent_store']     = isset($default_values['parent_id']) ? $this->storeRepository->getParentStore($default_values['parent_id']) : null;
            $options['parent_code_name'] = isset($options['parent_store']) ? $options['parent_store']['code'] . ' - ' . $options['parent_store']['name'] : null;
            $province                    = isset($default_values['province_id']) ? $this->provinceRepository->find($default_values['province_id']) : null;
            $district                    = isset($default_values['district_id']) ? $this->districtRepository->find($default_values['district_id']) : null;

            if (isset($province)) {
                $options['provinceDistricts']  = $province->district()->get();
                $options['provinceWards']      = $district->ward()->get();
                $provinceLocalities            = $this->organizationRepository->getLocalityByProvince($province->id)->pluck('id')->toArray(); // Lay dia ban theo tinh/tp
                $options['provinceLocalities'] = $options['userLocalities']->filter(function ($value) use ($provinceLocalities) { // Lay dia ban ma user duoc phep
                    return in_array($value->id, $provinceLocalities);
                });
            }

            $options['localityLines']                  = $this->lineRepository->getByLocality($default_values['organization_id'] ?? null);
            $lineStore                                 = $this->lineStoreService->getByNewStore($newStoreId)->first();
            $options['default_values']['line']         = $lineStore->line_id ?? null;
            $options['default_values']['number_visit'] = $lineStore->number_visit ?? null;
            $options['default_values']['localityName'] = $options['userLocalities']->firstWhere('id', $default_values['organization_id'] ?? '')?->name;
            $options['default_values']['newStoreId']   = $newStoreId;
            $options['default_values']['organization'] = $model->organization;
        }

        if ($currentRoute == 'admin.new-stores.edit' || $currentRoute == 'admin.new-stores.show' || $currentRoute == 'admin.new-stores.approve') {
            $arr                    = [
                'vat_number'  => $default_values['vat_number'] ?? '',
                'address'     => $default_values['address'] ?? '',
                'phone_owner' => $default_values['phone_owner'] ?? '',
                'name'        => $default_values['name'] ?? '',
                'locality_id' => $default_values['organization_id'] ?? ''
            ];
            $options['exist_store'] = $this->storeService->checkStoreExist($arr);
        }
        if (old('province_id')) {
            $province                      = $this->provinceRepository->find(old('province_id'));
            $options['provinceDistricts']  = $province->district()->get() ?? null;
            $provinceLocalities            = $this->organizationRepository->getLocalityByProvince(old('province_id'))->pluck('id')->toArray(); // Lay dia ban theo tinh/tp
            $options['provinceLocalities'] = $options['userLocalities']->filter(function ($value) use ($provinceLocalities) { // Lay dia ban ma user duoc phep
                return in_array($value->id, $provinceLocalities);
            });
            if (old('district_id')) {
                $district                 = $this->districtRepository->find(old('district_id'));
                $options['provinceWards'] = $district->ward()->get();
            }
        }
        if (old('parent_code_name')) $options['parent_code_name'] = old('parent_code_name');
        if (old('organization_id')) $options['localityLines'] = $this->lineRepository->getByLocality(old('organization_id'));
        if (old('line')) $options['default_values']['line'] = old('line');
        if (old('code')) {
            $options['default_values']['code'] = old('code');
        } else {
            if ($options['default_values']['code'] == '') {
                $options['default_values']['code'] = $this->storeService->generateCode($options['default_values']['province_id'], $options['default_values']['district_id'], $options['default_values']['type']);
            }
        }

        return $options;
    }

    // Admin, SA sua nha thuoc moi thi se duyet luon
    public function update($id, array $attributes = [])
    {
        $attributes['status']     = NewStore::STATUS_ACTIVE;
        $attributes['updated_by'] = Helper::currentUser()->id;
        $attributes['parent_id']  = $attributes['has_parent'] ? $attributes['parent_id'] : null;
        $images                   = $attributes['image_files'] ?? [];

        if (!empty($images)) {
            // Xoa file cu
            $old_files = $attributes['old_files'] ?? [];
            Storage::delete($old_files);
            $attributes['file_id'] = json_encode($this->storeService->uploadImagesStore($images));
        }
        if (!$attributes['has_parent']) {
            $attributes['parent_id']  = null;
            $attributes['vat_parent'] = null;
        } else {
            $attributes['parent_id']  = $attributes['parent_id'] ?? null;
            $attributes['vat_parent'] = $attributes['vat_parent'] ?? null;
        }

        $newStore = $this->repository->update($id, $attributes);
        if ($newStore) {
            $lineStore = $newStore->lineStore;
            $store     = $this->storeRepository->create($newStore->toArray()); // Tao ban ghi moi trong bang store
            $this->repository->update($id, ['store_id' => $store->id, 'code' => $store->code]); // Cap nhat store_id vao bang new_stores
            if ($lineStore) {
                $this->approveNewStoreChangeLineStore($lineStore, $store->id);
            }
        }
    }

    public function updateStoreByTDV(int $id, array $attributes = [])
    {
        $newStore                 = $this->model->find($id);
        $lineStore                = $newStore->lineStore;
        $attributes['status']     = NewStore::STATUS_INACTIVE;
        $attributes['updated_by'] = Helper::currentUser()->id;
        $attributes['parent_id']  = $attributes['has_parent'] ? $attributes['parent_id'] : null;
        $images                   = $attributes['image_files'] ?? [];

        if (!empty($images)) {
            // Xoa file cu
            $old_files = $attributes['old_files'] ?? [];
            Storage::delete($old_files);
            $attributes['file_id'] = json_encode($this->storeService->uploadImagesStore($images));
        }
        if (!$attributes['has_parent']) {
            $attributes['parent_id']  = null;
            $attributes['vat_parent'] = null;
        } else {
            $attributes['parent_id']  = $attributes['parent_id'] ?? null;
            $attributes['vat_parent'] = $attributes['vat_parent'] ?? null;
        }

        if ($lineStore) {
            $lineStore->update([
                'from'           => null,
                'to'             => null,
                'line_id'        => $attributes['line'] ?? null,
                'number_visit'   => $attributes['number_visit'] ?? LineStore::DEFAULT_NUMBER_VISIT,
                'reference_type' => LineStore::REFERENCE_TYPE_NEW_STORE,
                'updated_by'     => $attributes['updated_by'],
                'status'         => LineStore::STATUS_PENDING
            ]);
        }


        return $newStore ? $newStore->update($attributes) : null;
    }

    // Admin, SA duyet nha thuoc moi
    public function approve(int $id, array $attributes = [])
    {
        $newStore    = $this->model->find($id);
        $lineStore   = $newStore->load('lineStore')->lineStore ?? null;
        $currentUser = Helper::currentUser();

        if ($attributes['storeStatus'] == NewStore::STATUS_ACTIVE) {
            $storeData         = $newStore;
            $storeData->status = Store::STATUS_ACTIVE;
            $storeData->code   = $attributes['code'] ?? null;
            $store             = $this->storeRepository->create($storeData->toArray());

            $this->repository->update($id, [
                'code'       => $attributes['code'] ?? '',
                'status'     => NewStore::STATUS_ACTIVE,
                'updated_by' => $currentUser->id,
                'store_id'   => $store->id,
            ]);
            if ($lineStore) {
                $this->approveNewStoreChangeLineStore($lineStore, $store->id);
            }
        } else {
            $status = $attributes['storeStatus'] ?? NewStore::STATUS_INACTIVE;
            $newStore->update([
                'code'       => null,
                'status'     => $status,
                'updated_by' => $currentUser->id,
                'store_id'   => null,
            ]);

            if ($lineStore) {
                $this->lineStoreService->closeWhenNotApprovedOrDeleteNewStore($lineStore->id);
            }
        }
    }

    // Tao ban ghi line_stores moi + update ban ghi line_stores cua new_stores thanh active
    public function approveNewStoreChangeLineStore($lineStore = null, $storeId = null)
    {
        if (!$lineStore) return false;
        $lineStoreId = $lineStore->id;
        $lineId      = $lineStore->line_id;
        $numberVisit = $lineStore->number_visit;

        $this->lineStoreService->createLineStoreWhenApproveNewStore($lineId, $storeId, $numberVisit, $lineStoreId); // Tao ban ghi line_stores moi
        $lineStore->update([ // Cap nhat ban ghi line_store da co cua new_stores sang trang thai active
            'from'       => now()->toDateString(),
            'to'         => null,
            'status'     => LineStore::STATUS_ACTIVE,
            'updated_by' => Helper::currentUser()->id
        ]);
    }

    public function getTable($requestParams = [], $showOption = []): TableHelper
    {
        $showOption = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [
                "column" => "new_stores.created_at",
                "type"   => "DESC"
            ]
        ], $showOption);

        $currentUser   = Helper::currentUser();
        $requestParams = $this->getRequestParams($requestParams);
        $canEdit       = $currentUser->can('duyet_nha_thuoc_moi');
        $canView       = $currentUser->can('xem_nha_thuoc_moi');
        $results       = $this->repository->getByRequest(['user', 'userUpdate', 'province', 'district', 'ward'], $requestParams, $showOption);
        $cur_page      = $results->currentPage();
        $per_page      = $results->perPage();

        $results->getCollection()->transform(function ($item, $loopIndex) use ($canEdit, $canView, $cur_page, $per_page, $currentUser) {
            $item->stt               = ($loopIndex + 1) + ($cur_page - 1) * ($per_page);
            $item->status            = match ($item->status) {
                NewStore::STATUS_ALL => '<span class="badge bg-secondary rounded-3" style="padding: 5px 10px">' . NewStore::STATUS_TEXTS[NewStore::STATUS_ALL] . '</span>',
                NewStore::STATUS_ACTIVE => '<span class="badge bg-success rounded-3" style="padding: 5px 10px">' . NewStore::STATUS_TEXTS[NewStore::STATUS_ACTIVE] . '</span>',
                NewStore::STATUS_INACTIVE => '<span class="badge bg-warning rounded-3" style="padding: 5px 10px">' . NewStore::STATUS_TEXTS[NewStore::STATUS_INACTIVE] . '</span>',
                NewStore::STATUS_NOT_APPROVED => '<span class="badge bg-secondary rounded-3" style="padding: 5px 10px">' . NewStore::STATUS_TEXTS[NewStore::STATUS_NOT_APPROVED] . '</span>',
                default => '<span class="badge bg-warning rounded-3" style="padding: 5px 10px">' . NewStore::STATUS_TEXTS[NewStore::STATUS_INACTIVE] . '</span>',
            };
            $item->created_by        = $item->user['name'] ?? '';
            $item->updated_by        = $item->userUpdate['name'] ?? '';
            $item->created_at_format = Carbon::parse($item->created_at)->format('H:i, Y-m-d');
            $storeAddress            = $item->address;
            $provinceName            = $item?->province?->province_name;
            $districtName            = $item?->district?->district_name;
            $wardName                = $item?->ward?->ward_name;
            $addressInfo             = [
                $storeAddress,
                $wardName,
                $districtName,
                $provinceName,
            ];
            $item->address           = implode(' - ', $addressInfo);
            $item->features          = "";
            if ($canView) {
                if ($currentUser?->roles[0]?->name == User::ROLE_TDV) {
                    $item->features .= '<a class="btn btn-sm btn-icon"
                   href="' . route('admin.tdv.new-stores.show', $item->id) . '">
                    <i data-feather="file-text" class="font-medium-2 text-body"></i>
                </a>';
                } else {
                    $item->features .= '<a class="btn btn-sm btn-icon"
                   href="' . route('admin.new-stores.show', $item->id) . '">
                    <i data-feather="file-text" class="font-medium-2 text-body"></i>
                </a>';
                }
            }
            if ($canEdit) {
                $item->features .= '<a class="btn btn-sm btn-icon"
                   href="' . route('admin.new-stores.approve', $item->id) . '">
                    <i data-feather="edit" class="font-medium-2 text-body"></i>
                </a>';
            }

            return $item;
        });
        $nameTable = Helper::userCan('xem_nha_thuoc_moi') ? 'new_store-list' : '';

        return new TableHelper(
            collections: $results,
            nameTable: $nameTable,
        );
    }

    public function indexOptions(): array
    {
        $current_user      = Helper::currentUser();
        $user_organization = Helper::getUserOrganization();
        $requestName       = request('search.name') ?? null;
        $requestDivision   = request('search.division_id') ?? null;
        $requestLocality   = request('search.locality_ids') ?? null;
        $requestCreated    = request('search.created_by') ?? null;
        $requestStatus     = request('search.status') ?? NewStore::STATUS_INACTIVE;
        $requestDisable    = request('search.disable') ?? null;
        if ($requestName) {
            $requestLocality = null;
            $requestDivision = null;
            $requestStatus   = null;
            $requestDisable  = null;
        }

        $show_division_field = $show_locality_field = true;
        $show_tdv_field      = false;
        $count_division      = count($user_organization[Organization::TYPE_KHU_VUC] ?? []);
        $count_locality      = count($user_organization[Organization::TYPE_DIA_BAN] ?? []);
        if ($count_division == 1) {
            $show_division_field = false;
            $requestDivision     = array_shift($user_organization[Organization::TYPE_KHU_VUC]);
        }
        if ($count_locality == 1) {
            $show_locality_field = false;
        }
        if ($current_user?->roles?->first()->name == 'TDV') {
            $show_tdv_field = false;
        }

        $searchOptions   = [];
        $searchOptions[] = [
            'wrapClass'    => 'col-md-2',
            'type'         => 'text',
            'name'         => 'search[name]',
            'placeholder'  => 'Mã/Tên nhà thuốc',
            'defaultValue' => $requestName,
        ];
        if ($show_division_field) {
            $searchOptions[] = [
                'wrapClass'            => 'col-md-3',
                'type'                 => 'divisionPicker',
                'divisionPickerConfig' => [
                    'currentUser'     => true,
                    'activeTypes'     => [
                        Organization::TYPE_KHU_VUC,
                    ],
                    'excludeTypes'    => [
                        Organization::TYPE_DIA_BAN,
                    ],
                    'hasRelationship' => true,
                    'setup'           => [
                        'multiple'   => false,
                        'name'       => 'search[division_id]',
                        'class'      => '',
                        'id'         => 'division_id',
                        'attributes' => '',
                        'selected'   => $requestDivision
                    ],
                    'widthDefault'    => 'auto',
                ],
            ];
        }
        if ($show_locality_field) {
            $localityOptions = $requestDivision ? $this->organizationRepository->getLocalityByDivision($requestDivision)->pluck('name', 'id')->toArray() : [];
            $searchOptions[] = [
                'wrapClass'     => 'col-md-2',
                'type'          => 'selection',
                'name'          => 'search[locality_ids]',
                'defaultValue'  => $requestLocality,
                'id'            => 'form-locality_id',
                'options'       => ['' => '- Địa bàn -'] + $localityOptions,
                'other_options' => ['option_class' => 'ajax-locality-option'],
            ];
        }
        $option_tdv = ['' => '- TDV -'];
        if ($show_tdv_field) {
            if ($requestLocality) {
                $option_tdv += $this->organizationRepository->getUserByLocality($requestLocality)
                    ->pluck('name', 'id')->toArray();
            }
            $searchOptions[] = [
                'wrapClass'     => 'col-md-2',
                'type'          => 'selection',
                'name'          => 'search[created_by]',
                'defaultValue'  => $requestCreated,
                'id'            => 'form-created_by',
                'options'       => $option_tdv,
                'other_options' => ['option_class' => 'ajax-tdv-option'],
            ];
        }
        $searchOptions[] = [
            'wrapClass'    => 'col-md-2',
            'type'         => 'selection',
            'name'         => 'search[status]',
            'defaultValue' => $requestStatus,
            'id'           => 'form-status',
            'options'      => ['' => '- Trạng thái -'] + NewStore::STATUS_TEXTS,
        ];
        $searchOptions[] = [
            'wrapClass'    => 'col-md-2',
            'type'         => 'selection',
            'name'         => 'search[disable]',
            'defaultValue' => $requestDisable,
            'id'           => 'form-disable',
            'options'      => ['' => '- Trạng thái -', NewStore::STATUS_IS_DISABLE => NewStore::STATUS_IS_DISABLE_TEXT],
        ];

        return compact('searchOptions');
    }

    public function delete(int $id): bool
    {
        $newStore              = $this->repository->findOrFail($id);
        $newStore->updated_by  = Helper::currentUser()->id;
        $newStore->is_disabled = NewStore::STATUS_IS_DISABLE;
        return $newStore->save();
    }

    public function getTableForTdv($requestParams = [], $showOption = [])
    {
        $showOption    = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [
                "column" => "new_stores.created_at",
                "type"   => "DESC"
            ]
        ], $showOption);
        $currentUser   = Helper::currentUser();
        $requestParams = $this->getRequestParams($requestParams);
        $stores        = $this->repository->getByRequestTDV(['user', 'userUpdate', 'province', 'district', 'ward'], $requestParams, $showOption);
        $stores->map(function ($store) use ($requestParams, $currentUser) {
            $routeStoreDetail = route('admin.tdv.new-stores.show', ['id' => $store->id]);
            $storeName        = $store->name;
            $storeAddress     = $store->address;
            $provinceName     = $store?->province?->province_name;
            $districtName     = $store?->district?->district_name;
            $wardName         = $store?->ward?->ward_name;
            $addressInfo      = [
                $storeAddress,
                $wardName,
                $districtName,
                $provinceName,
            ];

            $store->store_info = "<div style='margin-top: 0.5rem;margin-bottom: 0.5rem;'>
                <div class='d-flex'>
                    <span class='fw-bolder'>
                        <a href='$routeStoreDetail'>$storeName</a>
                    </span>
                </div>";
            $store->store_info .= "<div class='mb-1'><b>ĐC: </b>" . implode(' - ', $addressInfo) . "</div>";
            $store->store_info .= "<div>";

            if ($store->created_by == $currentUser->id && $store->status == NewStore::STATUS_INACTIVE) {
                $store->store_info .= "<a href='" . route('admin.tdv.new-stores.edit', ['id' => $store->id]) . "' class='float-end badge bg-secondary mx-1'>Sửa NT</a>";
            }
            $store->store_info .= "<a href='$routeStoreDetail' class='float-end badge bg-secondary mx-1'>Chi tiết NT</a>";
            $store->store_info .= match ($store->status) {
                NewStore::STATUS_ALL => '<span class="badge bg-secondary rounded-3" style="padding: 5px 10px">' . NewStore::STATUS_TEXTS[NewStore::STATUS_ALL] . '</span>',
                NewStore::STATUS_ACTIVE => '<span class="badge bg-success rounded-3" style="padding: 5px 10px">' . NewStore::STATUS_TEXTS[NewStore::STATUS_ACTIVE] . '</span>',
                NewStore::STATUS_INACTIVE => '<span class="badge bg-warning rounded-3" style="padding: 5px 10px">' . NewStore::STATUS_TEXTS[NewStore::STATUS_INACTIVE] . '</span>',
                NewStore::STATUS_NOT_APPROVED => '<span class="badge bg-secondary rounded-3" style="padding: 5px 10px">' . NewStore::STATUS_TEXTS[NewStore::STATUS_NOT_APPROVED] . '</span>',
                default => '<span class="badge bg-warning rounded-3" style="padding: 5px 10px">' . NewStore::STATUS_TEXTS[NewStore::STATUS_INACTIVE] . '</span>',
            };
            $store->store_info .= '</div>';
            $store->store_info .= '</div>';

            return $store;
        });

        return new TableHelper(
            collections: $stores,
            nameTable: 'tdv-new-stores-list',
        );
    }

    /**
     * @param $requestParams
     * @return mixed
     */
    public function getRequestParams($requestParams)
    {
        $currentUser               = Helper::currentUser();
        $organizationOfCurrentUser = Helper::getUserOrganization($currentUser);
        $userLocalities            = $organizationOfCurrentUser[Organization::TYPE_DIA_BAN] ?? null; // Tat ca dia ban user duoc cho phep
        $roleName                  = $currentUser?->roles[0]?->name;
        $requestParams['status']   = $requestParams['status'] ?? [NewStore::STATUS_INACTIVE];
        $requestParams['disable']  = isset($requestParams['disable']) ? [$requestParams['disable']] : [NewStore::STATUS_UN_DISABLE];
        $search_division           = $requestParams['division_id'] ?? null;
        $search_locality           = $requestParams['locality_ids'] ?? null;

        if (!$search_division && !$search_locality) {
            $requestParams['locality_ids'] = $userLocalities;
        } elseif (!$search_division && $search_locality) {
            $requestParams['locality_ids'] = array($search_locality);
        } elseif ($search_division && !$search_locality) {
            $requestParams['locality_ids'] = $this->organizationRepository->getLocalityByDivision($search_division)->pluck('id')->toArray();
        } elseif ($search_division && $search_locality) {
            $requestParams['locality_ids'] = array($search_locality);
        }
        if ($roleName == User::ROLE_TDV) {
            $requestParams['created_by'] = $currentUser->id;
        }
        if (isset($requestParams['name'])) {
            $requestParams['status']       = null;
            $requestParams['disable']      = null;
            $requestParams['division_id']  = null;
            $requestParams['locality_ids'] = null;
        }

        return $requestParams;
    }

    public function checkLocalityPermission($newStoreModel = null, $currentUser = null): bool
    {
        $currentUser               = $currentUser ?? Helper::currentUser();
        $organizationOfCurrentUser = Helper::getUserOrganization();
        $roleName                  = $currentUser?->roles[0]?->name;
        $userLocalities            = $organizationOfCurrentUser[Organization::TYPE_DIA_BAN] ?? [];

        if (!$newStoreModel) return false;
        if ($roleName == User::ROLE_Admin) return true;

        $newStoreModel->load('organization');
        $localityStore = $newStoreModel->organization->id ?? null;

        if (!count($userLocalities) || !in_array($localityStore, $userLocalities)) {
            return false;
        }

        return true;
    }

    public function urlRedirect(): string
    {
        return Helper::userRoleName() == User::ROLE_TDV ? 'admin.tdv.new-stores.index' : 'admin.new-stores.index';
    }

    public function checkTDVCreated($newStoreId = null, $currentUser = null): bool
    {
        $currentUser = $currentUser ?? Helper::currentUser();
        if (isset($newStoreId)) {
            $item = $this->repository->checkUserCreated($currentUser->id, $newStoreId);
            return (bool)$item;
        } else {
            return false;
        }
    }
}
