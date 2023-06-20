<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Helpers\TableHelper;
use App\Models\File;
use App\Models\NewStore;
use App\Models\Organization;
use App\Models\Store;
use App\Models\StoreHistory;
use App\Models\User;
use App\Repositories\District\DistrictRepositoryInterface;
use App\Repositories\File\FileRepositoryInterface;
use App\Repositories\Line\LineRepositoryInterface;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\Province\ProvinceRepositoryInterface;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Repositories\StoreChange\StoreChangeRepositoryInterface;
use App\Models\StoreChange;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StoreChangeService extends BaseService
{
    protected StoreChangeRepositoryInterface $repository;
    protected StoreRepositoryInterface $storeRepository;
    protected StoreService $storeService;
    protected FileRepositoryInterface $fileRepository;
    protected ProvinceRepositoryInterface $provinceRepository;
    protected DistrictRepositoryInterface $districtRepository;
    protected OrganizationRepositoryInterface $organizationRepository;
    protected LineRepositoryInterface $lineRepository;
    protected LineStoreService $lineStoreService;
    protected UserService $userService;

    public function __construct(
        StoreChangeRepositoryInterface  $repository,
        StoreRepositoryInterface        $storeRepository,
        StoreService                    $storeService,
        FileRepositoryInterface         $fileRepository,
        ProvinceRepositoryInterface     $provinceRepository,
        DistrictRepositoryInterface     $districtRepository,
        LineRepositoryInterface         $lineRepository,
        LineStoreService                $lineStoreService,
        UserService                     $userService,
        OrganizationRepositoryInterface $organizationRepository)
    {
        parent::__construct();

        $this->repository             = $repository;
        $this->storeRepository        = $storeRepository;
        $this->storeService           = $storeService;
        $this->fileRepository         = $fileRepository;
        $this->provinceRepository     = $provinceRepository;
        $this->districtRepository     = $districtRepository;
        $this->organizationRepository = $organizationRepository;
        $this->lineRepository         = $lineRepository;
        $this->lineStoreService       = $lineStoreService;
        $this->userService            = $userService;
    }

    public function setModel()
    {
        return new StoreChange();
    }

    public function formOptions($model = null, $id = null): array
    {
        return $this->getFormOptions($model, $id);
    }

    public function formOptionsTDV($model = null): array
    {
        return $this->getFormOptions($model);
    }

    // Admin, SA sua nha thuoc thay doi
    public function update(int $id, array $attributes = [])
    {
        $storeChange = $this->model->find($id);
        if (!$storeChange) return false;
        $attributes['code']       = $storeChange->code;
        $attributes['updated_by'] = Helper::currentUser()->id;
        $attributes['status']     = StoreChange::STATUS_ACTIVE;

        if (!isset($attributes['has_parent'])) {
            $attributes['parent_id']  = null;
            $attributes['vat_parent'] = null;
        } else {
            $attributes['parent_id']  = $attributes['parent_id'] ?? null;
            $attributes['vat_parent'] = $attributes['vat_parent'] ?? null;
        }

        $storeChange = $this->repository->update($id, $attributes);
        $this->updateStore($storeChange);
        $this->createStoreHistory($storeChange->store_id);
    }

    // TDV sua nha thuoc thay doi
    public function updateTDV(int $id, array $attributes = [])
    {
        $storeChange = $this->model->find($id);
        if (!$storeChange) return false;
        $attributes['code']       = $storeChange->code;
        $attributes['status']     = StoreChange::STATUS_INACTIVE;
        $attributes['updated_by'] = Helper::currentUser()->id;
        if (!$attributes['has_parent']) {
            $attributes['parent_id']  = null;
            $attributes['vat_parent'] = null;
        } else {
            $attributes['parent_id']  = $attributes['parent_id'] ?? null;
            $attributes['vat_parent'] = $attributes['vat_parent'] ?? null;
        }

        return $storeChange->update($attributes);
    }

    // Admin, SA duyet nha thuoc thay doi
    public function approve(int $id, array $attributes = [])
    {
        $storeChange = $this->model->find($id);
        if (!$storeChange) return false;
        $attributes['updated_by'] = Helper::currentUser()->id;
        if ($attributes['status'] == StoreChange::STATUS_ACTIVE) {
            $storeChange = $this->repository->update($id, $attributes);
            $this->updateStore($storeChange);
            $this->createStoreHistory($storeChange->store_id);
        }
    }

    public function updateStore($storeChange = null)
    {
        if ($storeChange) {
            $arr           = $storeChange;
            $storeId       = $storeChange->store_id ?? null;
            $arr['status'] = (int)$storeChange['store_status'] ?? Store::STATUS_INACTIVE;
            $this->storeRepository->update($storeId, $arr->toArray());

            if ($arr['status'] == Store::STATUS_INACTIVE) {
                $this->repository->setNotApproveByStore($storeId);
            }
        }
    }

    public function createStoreHistory($storeId = null)
    {
        $store = $this->storeRepository->find($storeId);
        $item  = [
            'store_id'   => $storeId,
            'store_data' => json_encode($store),
            'created_by' => Auth::user()->id,
        ];

        StoreHistory::query()->create($item);
    }

    public function getTable($requestParams = [], $showOption = []): TableHelper
    {
        $showOption    = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [
                "column" => "stores.created_at",
                "type"   => "DESC"
            ]
        ], $showOption);
        $currentUser   = Helper::currentUser();
        $requestParams = $this->getRequestParams($requestParams);
        $canEdit       = $currentUser->can('duyet_nha_thuoc_thay_doi');
        $canView       = $currentUser->can('xem_nha_thuoc_thay_doi');
        $results       = $this->repository->getByRequest(['user', 'province', 'district', 'ward'], $requestParams, $showOption);
        $cur_page      = $results->currentPage();
        $per_page      = $results->perPage();

        $results->getCollection()->transform(function ($item, $loopIndex) use ($canEdit, $canView, $cur_page, $per_page, $currentUser) {
            $item->stt               = ($loopIndex + 1) + ($cur_page - 1) * ($per_page);
            $item->status            = match ($item->status) {
                StoreChange::ALL_STATUS => '<span class="badge bg-secondary rounded-3" style="padding: 5px 10px">' . StoreChange::STATUS_TEXTS[StoreChange::ALL_STATUS] . '</span>',
                StoreChange::STATUS_ACTIVE => '<span class="badge bg-success rounded-3" style="padding: 5px 10px">' . StoreChange::STATUS_TEXTS[StoreChange::STATUS_ACTIVE] . '</span>',
                StoreChange::STATUS_INACTIVE => '<span class="badge bg-warning rounded-3" style="padding: 5px 10px">' . StoreChange::STATUS_TEXTS[StoreChange::STATUS_INACTIVE] . '</span>',
                StoreChange::STATUS_NOT_APPROVE => '<span class="badge bg-secondary rounded-3" style="padding: 5px 10px">' . StoreChange::STATUS_TEXTS[StoreChange::STATUS_NOT_APPROVE] . '</span>',
                default => '<span class="badge bg-warning rounded-3" style="padding: 5px 10px">' . StoreChange::STATUS_TEXTS[StoreChange::STATUS_INACTIVE] . '</span>',
            };
            $item->phone_owner       = $item->phone_owner ?? '';
            $item->created_by        = $item->user['name'] ?? '';
            $item->updated_by        = $item->userUpdate['name'] ?? '';
            $item->created_at_format = $item->created_at->format('H:i, d-m-Y');
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
                   href="' . route('admin.tdv.store-changes.show', $item->id) . '">
                    <i data-feather="file-text" class="font-medium-2 text-body"></i>
                </a>';
                } else {
                    $item->features .= '<a class="btn btn-sm btn-icon"
                   href="' . route('admin.store_changes.show', $item->id) . '">
                    <i data-feather="file-text" class="font-medium-2 text-body"></i>
                </a>';
                }
            }
            if ($canEdit) {
                $item->features .= '<a class="btn btn-sm btn-icon"
                   href="' . route('admin.store_changes.approve', $item->id) . '">
                    <i data-feather="edit" class="font-medium-2 text-body"></i>
                </a>';
            }

            return $item;
        });
        $nameTable = Helper::userCan('xem_nha_thuoc_moi') ? 'store-change-list' : '';

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
        $requestStatus     = request('search.status') ?? StoreChange::STATUS_INACTIVE;
        if ($requestName) {
            $requestLocality = null;
            $requestDivision = null;
            $requestStatus   = null;
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
            'options'      => ['' => '- Trạng thái -'] + StoreChange::STATUS_TEXTS,
        ];

        return compact('searchOptions');
    }

    public function getTableForTdv($requestParams = [], $showOption = []): TableHelper
    {
        $showOption    = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [
                "column" => "stores.created_at",
                "type"   => "DESC"
            ]
        ], $showOption);
        $currentUser   = Helper::currentUser();
        $requestParams = $this->getRequestParams($requestParams);
        $canView       = $currentUser->can('xem_nha_thuoc_thay_doi');
        $results       = $this->repository->getByRequestTDV(['user', 'province', 'district', 'ward'], $requestParams, $showOption);

        $results->map(function ($item) use ($requestParams, $canView, $currentUser) {
            $routeStoreDetail = route('admin.tdv.store-changes.show', ['id' => $item->id]);
            $storeName        = $item->name;
            $storeAddress     = $item->address;
            $provinceName     = $item?->province?->province_name;
            $districtName     = $item?->district?->district_name;
            $wardName         = $item?->ward?->ward_name;
            $addressInfo      = [
                $storeAddress,
                $wardName,
                $districtName,
                $provinceName,
            ];

            $storePhoneOwner  = $item->phone_owner;
            $storeCreatedAt   = Carbon::parse($item->created_at)->format('H:i:s, Y-m-d');
            $item->store_info = $canView ? "<div style='margin-top: 0.5rem;margin-bottom: 0.5rem;'>
                <div class='d-flex'>
                    <span class='fw-bolder'>
                        <a href='$routeStoreDetail'>$storeName</a>
                    </span>
                </div>" : '';
            $item->store_info .= "<div class='mb-1'><b>ĐC: </b>" . implode(' - ', $addressInfo) . "</div>";
            $item->store_info .= "<div class='mb-1'><b>SĐT: </b>$storePhoneOwner</div>";
            $item->store_info .= "<div class='mb-1'></div><b>Thời gian: </b>$storeCreatedAt</div>";
            $item->store_info .= "<div>";
            if ($item->status == StoreChange::STATUS_INACTIVE && $item->created_by == $currentUser->id) {
                $item->store_info .= "<a href='" . route('admin.tdv.store-changes.edit', ['id' => $item->id]) . "' class='float-end badge bg-secondary mx-1'>Sửa NT</a>";
            }
            $item->store_info .= $canView ? "<a href='$routeStoreDetail' class='float-end badge bg-secondary mx-1'>Chi tiết NT</a>" : '';
            $item->store_info .= match ($item->status) {
                StoreChange::ALL_STATUS => '<span class="badge bg-secondary rounded-3" style="padding: 5px 10px">' . StoreChange::STATUS_TEXTS[StoreChange::ALL_STATUS] . '</span>',
                StoreChange::STATUS_ACTIVE => '<span class="badge bg-success rounded-3" style="padding: 5px 10px">' . StoreChange::STATUS_TEXTS[StoreChange::STATUS_ACTIVE] . '</span>',
                StoreChange::STATUS_INACTIVE => '<span class="badge bg-warning rounded-3" style="padding: 5px 10px">' . StoreChange::STATUS_TEXTS[StoreChange::STATUS_INACTIVE] . '</span>',
                StoreChange::STATUS_NOT_APPROVE => '<span class="badge bg-secondary rounded-3" style="padding: 5px 10px">' . StoreChange::STATUS_TEXTS[StoreChange::STATUS_NOT_APPROVE] . '</span>',
                default => '<span class="badge bg-warning rounded-3" style="padding: 5px 10px">' . StoreChange::STATUS_TEXTS[StoreChange::STATUS_INACTIVE] . '</span>',
            };
            $item->store_info .= '</div>';
            $item->store_info .= '</div>';

            return $item;
        });
        $nameTable = Helper::userCan('xem_nha_thuoc_moi') ? 'tdv-store-changes-list' : '';

        return new TableHelper(
            collections: $results,
            nameTable: $nameTable,
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
        $requestParams['status']   = $requestParams['status'] ?? [StoreChange::STATUS_INACTIVE];
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
            $requestParams['division_id']  = null;
            $requestParams['locality_ids'] = null;
        }

        return $requestParams;
    }

    public function getFormOptions($model, $id = null)
    {
        $options                                           = parent::formOptions($model);
        $options['storeType']                              = Store::STORE_TYPE;
        $options['storeStatus']                            = Store::STATUS_TEXTS;
        $options['provinces']                              = $this->provinceRepository->all() ?? null;
        $options['userLocalities']                         = $this->userService->getUserLocalities(); // Tat ca dia ban user duoc phep
        $options['userProvinces']                          = $this->userService->userProvinces(); // Tat ca tinh/tp user duoc phep
        $options['default_values']                         = $model;
        $options['default_values']['province']             = $model->province;
        $options['default_values']['district']             = $model->district;
        $options['default_values']['ward']                 = $model->ward;
        $options['default_values']['organization']         = $model->organization;
        $options['default_values']['user']                 = $model->user;
        $options['default_values']['parent_store_address'] = $model->getFullAddressAttribute();
        $options['default_values']['vat_info']             = $model->getVatInfo();
        $options['userUpdate']                             = $model->userUpdate;
        $default_values                                    = $options['default_values'];
        $currentRoute                                      = request()->route()->getName();

        if ($currentRoute == 'admin.store_changes.approve' ||
            $currentRoute == 'admin.store_changes.show' ||
            $currentRoute == 'admin.tdv.store-changes.show') {
            $locality_id                           = $options['default_values']['organization_id'] ?? 0;
            $options['default_values']['tdv']      = $this->organizationRepository->getUserByLocality($locality_id) ?? null;
            $options['default_values']['tdv_name'] = $options['default_values']['tdv'] ? $options['default_values']['tdv']->pluck('name')->toArray() : [];
            $file_ids                              = $options['default_values']['file_id'] ? json_decode($options['default_values']['file_id']) : [];
            $options['default_values']['files']    = File::query()->whereIn('id', $file_ids)->get() ?? null;
            $parentStoreId                         = $options['default_values']['parent_id'] ?? null;
            if ($parentStoreId) {
                $options['default_values']['parent_store'] = $this->storeRepository->find($parentStoreId, ['province', 'district', 'ward']) ?? null;
            } else {
                $options['default_values']['parent_store'] = null;
            }

            // Current store
            $options['current_store']                         = $this->storeRepository->find($options['default_values']['store_id'], ['province', 'district', 'ward', 'organization'], true);
            $options['current_store']['tdv']                  = $this->organizationRepository->getUserByLocality($options['current_store']['organization_id'] ?? 0);
            $options['current_store']['tdv_name']             = isset($options['current_store']['tdv']) ? $options['current_store']['tdv']->pluck('name')->toArray() : [];
            $options['current_store']['parent_store']         = Store::query()->find($options['current_store']['parent_id'] ?? 0) ?? null;
            $options['current_store']['parent_store_address'] = isset($options['current_store']['parent_store']) ? $options['current_store']['parent_store']->getFullAddressAttribute() ?? null : null;
            $file2_ids                                        = isset($options['current_store']['file_id']) ? json_decode($options['current_store']['file_id']) : [];
            $options['current_store']['files']                = File::query()->whereIn('id', $file2_ids)->get() ?? null;
            $options['current_store']['creator']              = $options['current_store']->creator;
            $options['current_store']['vat_info']             = $options['current_store']->getVatInfo();
            $options['current_store']['creator']              = $options['current_store']->creator;
            $options['compareData']                           = $this->compareData($options['current_store']->toArray(), $options['default_values']->toArray());
        }
        if ($currentRoute == 'admin.tdv.store-changes.edit' || $currentRoute == 'admin.store_changes.edit') {
            $options['line']             = $model->lineStore->line ?? null;
            $arrFileId                   = $default_values['file_id'] ? json_decode($default_values['file_id'], true) : [];
            $options['files']            = $this->fileRepository->getByArrId($arrFileId);
            $options['parent_code_name'] = $model->store->getCodeAndName();
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

            $options['localityLines'] = $this->lineRepository->getByLocality($default_values['organization_id'] ?? null);
        }
        if ($currentRoute == 'admin.tdv.store-changes.edit' ||
            $currentRoute == 'admin.tdv.store-changes.show' ||
            $currentRoute == 'admin.store_changes.edit' ||
            $currentRoute == 'admin.store_changes.approve' ||
            $currentRoute == 'admin.store_changes.show'
        ) {
            $arr                    = [
                'vat_number'  => $options['default_values']['vat_number'] ?? '',
                'address'     => $options['default_values']['address'] ?? '',
                'phone_owner' => $options['default_values']['phone_owner'] ?? '',
                'name'        => $options['default_values']['name'] ?? '',
                'locality_id' => $options['default_values']['organization_id'] ?? '',
                'excludeId'   => $model->store_id ?? '',
            ];
            $options['exist_store'] = $this->storeService->checkStoreExist($arr);
        }

        if (old('province_id')) {
            $province                  = $this->provinceRepository->find(old('province_id')) ?? null;
            $options['userDistricts']  = $province->district()->get() ?? null;
            $options['userLocalities'] = $this->organizationRepository->getLocalityByProvince(old('province_id'))->pluck('id')->toArray();
            if (old('district_id')) {
                $district             = $this->districtRepository->find(old('district_id')) ?? null;
                $options['userWards'] = $district->ward()->get() ?? null;
            }
        }
        if (old('parent_code_name')) $options['parent_code_name'] = old('parent_code_name');
        if (old('organization_id')) $options['localityLines'] = $this->lineRepository->getByLocality(old('organization_id'));
        if (old('line')) $options['default_values']['line'] = old('line');
        if (old('number_visit')) $options['default_values']['number_visit'] = old('number_visit');

        return $options;
    }

    public function compareData($store = [], $storeChange = []): array
    {
        $result                       = [];
        $arrTypeSkip                  = ['array', 'object'];
        $store['type_name']           = $store['type'] ? Store::STORE_TYPE[$store['type']] : null;
        $storeChange['type_name']     = $storeChange['type'] ? Store::STORE_TYPE[$storeChange['type']] : null;
        $store['show_web_text']       = $store['show_web'] ? "Có" : "";
        $storeChange['show_web_text'] = $storeChange['show_web'] ? "Có" : "Không";
        $store['status_text']         = isset($store['status']) ? Store::STATUS_TEXTS[$store['status']] : null;
        $storeChange['status_text']   = isset($storeChange['store_status']) ? Store::STATUS_TEXTS[$storeChange['store_status']] : null;

        foreach ($store as $key => $value) {
            $typeOfValue = gettype($value);
            if (!in_array($typeOfValue, $arrTypeSkip)) {
                $result['store'][$key] = $value;

                if (isset($storeChange[$key])) {
                    $result['storeChange'][$key] = $value == $storeChange[$key] ? $value : '<div class="text-danger">' . $storeChange[$key] . '</div>';
                } else {
                    $result['storeChange'][$key] = null;
                }
            }
        }

        // Kinh do, vi do
        $arrLatLngStore = $arrLatLngStoreChange = [];
        isset($store['lng']) && $store['lng'] ? $arrLatLngStore[] = $store['lng'] : null;
        isset($store['lat']) && $store['lat'] ? $arrLatLngStore[] = $store['lat'] : null;
        isset($storeChange['lng']) && $storeChange['lng'] ? $arrLatLngStoreChange[] = $storeChange['lng'] : null;
        isset($storeChange['lat']) && $storeChange['lat'] ? $arrLatLngStoreChange[] = $storeChange['lat'] : null;
        if (count($arrLatLngStore) || count($arrLatLngStoreChange)) {
            $result['store']['lng_lat']       = count($arrLatLngStore) ? implode(', ', $arrLatLngStore) : null;
            $result['storeChange']['lng_lat'] = $arrLatLngStore == $arrLatLngStoreChange ? implode(', ', $arrLatLngStoreChange) :
                '<div class="text-danger">' . implode(', ', $arrLatLngStoreChange) . '</div>';
        }

        // Dia chi
        $storeProvinceName        = $store['province']['province_name'] ?? null;
        $storeDistrictName        = $store['district']['district_name'] ?? null;
        $storeWardName            = $store['ward']['ward_name'] ?? null;
        $storeAddress             = $store['address'] ?? null;
        $storeAddressInfo         = [$storeAddress, $storeWardName, $storeDistrictName, $storeProvinceName];
        $storeChangeProvinceName  = $storeChange['province']['province_name'] ?? null;
        $storeChangeDistrictName  = $storeChange['district']['district_name'] ?? null;
        $storeChangeWardName      = $storeChange['ward']['ward_name'] ?? null;
        $storeChangeAddress       = $storeChange['address'] ?? null;
        $storeChangeAddressInfo   = [];
        $storeChangeAddressInfo[] = $storeChangeAddress !== $storeAddress ? '<span class="text-danger">' . $storeChangeAddress . '</span>' : $storeChangeAddress;
        $storeChangeAddressInfo[] = $storeChangeWardName !== $storeWardName ? '<span class="text-danger">' . $storeChangeWardName . '</span>' : $storeChangeWardName;
        $storeChangeAddressInfo[] = $storeChangeDistrictName !== $storeDistrictName ? '<span class="text-danger">' . $storeChangeDistrictName . '</span>' : $storeChangeDistrictName;
        $storeChangeAddressInfo[] = $storeChangeProvinceName !== $storeProvinceName ? '<span class="text-danger">' . $storeChangeProvinceName . '</span>' : $storeChangeProvinceName;

        if (count($storeAddressInfo) || count($storeChangeAddressInfo)) {
            $result['store']['full_address']       = implode(' - ', $storeAddressInfo);
            $result['storeChange']['full_address'] = implode(' - ', $storeChangeAddressInfo);
        }

        // Dia ban
        $storeLocalityName                      = $store['organization']['name'] ?? null;
        $storeChangeLocalityName                = $storeChange['organization']['name'] ?? null;
        $result['store']['locality_name']       = $storeLocalityName;
        $result['storeChange']['locality_name'] = $storeChangeLocalityName !== $storeLocalityName ? '<div class="text-danger">' . $storeChangeLocalityName . '</div>' : $storeChangeLocalityName;

        // Nha thuoc cha
        $storeChangeParentText                = $this->getStrParentStore($storeChange['parent_store']);
        $storeParentText                      = $this->getStrParentStore($store['parent_store']);
        $result['store']['parent_text']       = $storeParentText;
        $result['storeChange']['parent_text'] = $storeParentText == $storeChangeParentText ? $storeChangeParentText :
            '<div class="text-danger">' . $storeChangeParentText . '</div>';

        // Trinh duoc vien
        $storeChangeTDV                    = !empty($storeChange['tdv_name']) ? implode(',', $storeChange['tdv_name']) : '';
        $storeTDV                          = !empty($store['tdv_name']) ? implode(',', $store['tdv_name']) : '';
        $result['store']['tdv_text']       = $storeTDV;
        $result['storeChange']['tdv_text'] = $storeTDV == $storeChangeTDV ? $storeChangeTDV : '<div class="text-danger">' . $storeChangeTDV . '</div>';

        // Nguoi tao
        $storeCreator                          = $store['creator']['name'] ?? null;
        $storeChangeCreator                    = $storeChange['user']['name'] ?? null;
        $result['store']['creator_name']       = $storeCreator;
        $result['storeChange']['creator_name'] = $storeCreator == $storeChangeCreator ? $storeChangeCreator :
            '<div class="text-danger">' . $storeChangeCreator . '</div>';

        return $result;
    }

    /**
     * @param mixed $store
     * @return string
     */
    public function getStrParentStore(mixed $store): string
    {
        $parentText = "";
        if ($store) {
            $parentAddressInfo = [
                $store->address,
                $store->ward?->ward_name,
                $store->district?->district_name,
                $store->province?->province_name,
            ];

            $parentText = $parentText . ($store->code != '' ? $store->code . ' - ' : '');
            $parentText = $parentText . ($store->name != '' ? $store->name . ' - ' : '');
            $parentText = $parentText . implode(' - ', $parentAddressInfo);
        }

        return $parentText;
    }

    public function checkLocalityPermission($storeChangeModel = null, $currentUser = null): bool
    {
        $currentUser               = $currentUser ?? Helper::currentUser();
        $organizationOfCurrentUser = Helper::getUserOrganization();
        $roleName                  = $currentUser?->roles[0]?->name;
        $userLocalities            = $organizationOfCurrentUser[Organization::TYPE_DIA_BAN] ?? [];

        if (!$storeChangeModel) return false;
        if ($roleName == User::ROLE_Admin) return true;

        $storeChangeModel->load('organization');
        $localityStore = $storeChangeModel->organization->id ?? null;

        if (!count($userLocalities) || !in_array($localityStore, $userLocalities)) {
            return false;
        }

        return true;
    }

    public function urlRedirect(): string
    {
        return Helper::userRoleName() == User::ROLE_TDV ? 'admin.tdv.store-changes.index' : 'admin.store_changes.index';
    }

    public function checkTDVCreated($storeChangeId = null, $currentUser = null): bool
    {
        $currentUser = $currentUser ?? Helper::currentUser();
        if (isset($storeChangeId)) {
            $item = $this->repository->checkUserCreated($currentUser->id, $storeChangeId);
            return (bool)$item;
        } else {
            return false;
        }
    }
}
