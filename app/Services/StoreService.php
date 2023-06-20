<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Helpers\TableHelper;
use App\Models\File;
use App\Models\Line;
use App\Models\LineStore;
use App\Models\NewStore;
use App\Models\Organization;
use App\Models\ProductGroup;
use App\Models\Province;
use App\Models\Store;
use App\Models\StoreChange;
use App\Models\User;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\NewStore\NewStoreRepositoryInterface;
use App\Repositories\ProductGroup\ProductGroupRepositoryInterface;
use App\Repositories\ReportRevenueOrder\ReportRevenueOrderRepositoryInterface;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Repositories\Province\ProvinceRepositoryInterface;
use App\Repositories\District\DistrictRepositoryInterface;
use App\Repositories\StoreChange\StoreChangeRepositoryInterface;
use App\Repositories\StoreOrder\StoreOrderRepositoryInterface;
use App\Repositories\File\FileRepositoryInterface;
use App\Repositories\Line\LineRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StoreService extends BaseService
{
    protected $repository;
    protected $organizationRepository;
    protected $newStoreRepository;
    protected $storeChangeRepository;
    protected $provinceRepository;
    protected $districtRepository;
    protected $storeOrderRepository;
    protected $reportRevenueOrderRepository;
    protected $fileRepository;
    protected $lineRepository;
    protected $lineStoreService;
    protected $productGroupRepository;
    protected $reportRevenueStoreRankService;

    public function __construct(
        StoreRepositoryInterface              $repository,
        OrganizationRepositoryInterface       $organizationRepository,
        NewStoreRepositoryInterface           $newStoreRepository,
        StoreChangeRepositoryInterface        $storeChangeRepository,
        ProvinceRepositoryInterface           $provinceRepository,
        DistrictRepositoryInterface           $districtRepository,
        StoreOrderRepositoryInterface         $storeOrderRepository,
        ReportRevenueOrderRepositoryInterface $reportRevenueOrderRepository,
        FileRepositoryInterface               $fileRepository,
        LineRepositoryInterface               $lineRepository,
        ReportRevenueStoreRankService         $reportRevenueStoreRankService,
        ProductGroupRepositoryInterface       $productGroupRepository,
        LineStoreService                      $lineStoreService,
    )
    {
        parent::__construct();

        $this->repository                    = $repository;
        $this->organizationRepository        = $organizationRepository;
        $this->newStoreRepository            = $newStoreRepository;
        $this->storeChangeRepository         = $storeChangeRepository;
        $this->provinceRepository            = $provinceRepository;
        $this->districtRepository            = $districtRepository;
        $this->storeOrderRepository          = $storeOrderRepository;
        $this->reportRevenueOrderRepository  = $reportRevenueOrderRepository;
        $this->fileRepository                = $fileRepository;
        $this->lineRepository                = $lineRepository;
        $this->reportRevenueStoreRankService = $reportRevenueStoreRankService;
        $this->productGroupRepository        = $productGroupRepository;
        $this->lineStoreService              = $lineStoreService;
    }

    public function getModel()
    {
        return new Store();
    }

    public function setModel()
    {
        return new Store();
    }

    public function formOptions($model = null): array
    {
        return $this->getFormOptions($model);
    }

    public function formOptionsTDV($model = null): array
    {
        $options                    = $this->getFormOptions($model);
        $currentUser                = Helper::currentUser();
        $options['canAddEditStore'] = $currentUser->canany(['tdv_sua_nha_thuoc', 'tdv_them_nha_thuoc']);
        $currentRoute               = request()->route()->getName();

        if ($currentRoute == 'admin.tdv.store.index') {
            $options['months'] = ['' => '- Tháng -'];

            for ($i = 1; $i <= 12; $i++) {
                $options['months'][$i] = "Tháng $i";
            }
            $maxBookingAt     = now()->format('Y');
            $minBookingAt     = (integer)Carbon::create($this->storeOrderRepository->min('booking_at'))->format('Y');
            $options['years'] = ['' => '- Năm -'];

            for ($j = $minBookingAt; $j <= $maxBookingAt; $j++) {
                $options['years'][$j] = "$j";
            }
            $options['default_values']['month']                = request('search.month') ?? Carbon::now()->month;
            $options['default_values']['year']                 = request('search.year') ?? Carbon::now()->year;
            $options['default_values']['number_day_not_order'] = request('search.number_day_not_order') ?? null;
            $options['default_values']['not_enough_visit']     = request('search.not_enough_visit') ?? null;
        }

        if (request('search.name') !== null) {
            $options['default_values']['year']                 = null;
            $options['default_values']['month']                = null;
            $options['default_values']['number_day_not_order'] = null;
            $options['default_values']['not_enough_visit']     = null;
        }

        return $options;
    }

    public function create(array $attributes = [])
    {
        $currentUser              = Helper::currentUser();
        $roleName                 = $currentUser?->roles[0]?->name;
        $images                   = $attributes['image_files'] ?? [];
        $attributes['file_id']    = json_encode($this->uploadImagesStore($images)) ?? null;
        $attributes['created_by'] = Auth::user()->id;

        if (!$attributes['has_parent']) {
            $attributes['parent_id'] = null;
        }
        $attributes                    = $this->trimValue($attributes);
        $lineStoreItem                 = [];
        $lineStoreItem['line_id']      = $attributes['line'];
        $lineStoreItem['number_visit'] = $attributes['number_visit'] ?? LineStore::DEFAULT_NUMBER_VISIT;

        if ($roleName == User::ROLE_TDV) {
            $store                           = $this->createNewStoreByTDV($attributes);
            $lineStoreItem['store_id']       = $store->id;
            $lineStoreItem['reference_type'] = LineStore::REFERENCE_TYPE_NEW_STORE;
            $lineStoreItem['status']         = LineStore::STATUS_PENDING;
        } else {
            $store                           = $this->repository->create($attributes);
            $lineStoreItem['store_id']       = $store->id;
            $lineStoreItem['reference_type'] = LineStore::REFERENCE_TYPE_STORE;
            $lineStoreItem['status']         = LineStore::STATUS_ACTIVE;
        }
        $this->lineStoreService->create($lineStoreItem);

        return $store;
    }

    public function createNewStoreByTDV(array $attributes = [])
    {
        $attributes['status'] = NewStore::STATUS_INACTIVE;
        return $this->newStoreRepository->create($attributes);
    }

    public function update(int $id, array $attributes = [])
    {
        $store = $this->repository->find($id);
        if (!$store) return false;
        $attributes         = $this->handleUpdateData($store, $attributes);
        $currentLineStore   = $this->lineStoreService->getLineStoreRunningByStore($store->id)->first() ?? 0;
        $currentLineStoreId = isset($currentLineStore->id) ? intval($currentLineStore->id) : 0;
        $currentLineId      = isset($currentLineStore->line_id) ? intval($currentLineStore->line_id) : null;
        $currentNumberVisit = isset($currentLineStore->number_visit) ? intval($currentLineStore->number_visit) : null;
        $updateLineId       = isset($attributes['line']) ? intval($attributes['line']) : null;
        $updateNumberVisit  = isset($attributes['number_visit']) ? intval($attributes['number_visit']) : LineStore::DEFAULT_NUMBER_VISIT;

        if (Helper::userRoleName() == User::ROLE_TDV) {
            // TDV sua nha thuoc
            $attributes['code'] = $store->code;
            $compareData        = $this->compareData($attributes, $store->toArray());

            if (!$compareData) {
                $this->createStoreChangeByTDV($id, $attributes);
            }

            if ($currentLineId !== $updateLineId || $currentNumberVisit !== $updateNumberVisit) { // Cap nhat nha thuoc thay doi tuyen: ASM duyet
                $this->lineStoreService->tdvEditLineInStore($store->id, $updateLineId, $updateNumberVisit);
            }
        } else {
            // Admin, SA sua nha thuoc
            if ($currentLineId !== $updateLineId || $currentNumberVisit !== $updateNumberVisit) {
                $this->lineStoreService->storeEditLine($currentLineStoreId, [
                    'line_id'      => $updateLineId,
                    'store_id'     => $store->id,
                    'number_visit' => $updateNumberVisit,
                ]);
            }

            $status = (int)$attributes['status'] ?? Store::STATUS_INACTIVE;
            if ($status == Store::STATUS_INACTIVE) {
                $this->storeChangeRepository->setNotApproveByStore($store->id);
            }

            return $store->update($attributes);
        }
    }

    public function handleUpdateData($store = null, array $attributes = []): array
    {
        $images                   = $attributes['image_files'] ?? [];
        $attributes['updated_by'] = Auth::user()->id;

        if (!empty($images)) { // Xoa file cu
            $old_files = $attributes['old_files'] ?? [];
            Storage::delete($old_files);
            $attributes['file_id'] = json_encode($this->uploadImagesStore($images));
        } else {
            $attributes['file_id'] = $store->file_id;
        }
        if (!$attributes['has_parent']) {
            $attributes['parent_id']  = null;
            $attributes['vat_parent'] = null;
        } else {
            $attributes['parent_id']  = $attributes['parent_id'] ?? null;
            $attributes['vat_parent'] = $attributes['vat_parent'] ?? null;
        }

        return $attributes;
    }

    public function createStoreChangeByTDV($store_id, array $attributes = [])
    {
        $attributes['store_id']     = $store_id;
        $attributes['store_status'] = $attributes['status'];
        $attributes['status']       = StoreChange::STATUS_INACTIVE;
        $attributes['created_by']   = Auth::user()->id;
        return $this->storeChangeRepository->create($attributes);
    }

    public function uploadImagesStore($images): ?array
    {
        $fileIds = [];
        if (!empty($images)) {
            foreach ($images as $image) {
                $mime_type = $image->getMimeType();
                $file_name = $image->getClientOriginalName();
                $path      = $image->store('public/images/stores');
                $path      = str_replace('public/', '', $path);
                $file      = File::create([
                    'mime_type'  => $mime_type,
                    'name'       => $file_name,
                    'source'     => $path,
                    'created_by' => Auth::id(),
                ]);
                $fileIds[] = $file->id;
            }
        }
        return $fileIds ?? null;
    }

    public function generateCode($provinceId, $districtId, $storeType): string
    {
        if ($provinceId == '' || $districtId == '' || $storeType == '') return '';
        if ($storeType == Store::TYPE_MARKET) {
            $prefix_code = Store::PREFIX_MARKET;
        } else {
            $prefix_code = Helper::getPrefixCode($provinceId, $districtId);
        }

        $maxCode = Store::query()
            ->select('code')
            ->where('code', 'LIKE', $prefix_code . '%')
            ->get()
            ->map(function ($store) {
                $exp = "/.*(?:\D|^)(\d+)/";
                preg_match($exp, $store->code, $matches);
                $store->max_number = $matches[1] ?? 0;
                return $store;
            })
            ->max('max_number');

        $newCode    = $maxCode + 1;
        $newCode    = str_pad($newCode, 4, '0', STR_PAD_LEFT);
        $existStore = $this->repository->getByCode($newCode);
        if ($existStore->isNotEmpty()) {
            self::generateCode($provinceId, $districtId, $storeType);
        }

        return $prefix_code . $newCode;
    }

    public function getTable($requestParams = [], $showOption = [])
    {
        $showOption = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [
                "column" => "created_at",
                "type"   => "DESC"
            ]
        ], $showOption);

        $currentUser   = Helper::currentUser();
        $requestParams = $this->getRequestParams($requestParams);
        $canEdit       = $currentUser->canany(['sua_nha_thuoc']);
        $canView       = $currentUser->canany(['xem_nha_thuoc']);
        $results       = $this->repository->getByRequest(['province', 'district', 'ward', 'organization', 'organization.users'], $requestParams, $showOption);
        $cur_page      = $results->currentPage();
        $per_page      = $results->perPage();

        $results->getCollection()->transform(function ($item, $loopIndex) use ($canEdit, $canView, $cur_page, $per_page) {
            $item->stt = ($loopIndex + 1) + ($cur_page - 1) * ($per_page);
            if ($item->status == Store::STATUS_ACTIVE) {
                $item->customStatus = '<span class="badge bg-success rounded-3" style="padding: 5px 10px">' . Store::STATUS_TEXTS[Store::STATUS_ACTIVE] . '</span>';
            } elseif ($item->status == Store::STATUS_INACTIVE) {
                $item->customStatus = '<span class="badge bg-secondary rounded-3" style="padding: 5px 10px">' . Store::STATUS_TEXTS[Store::STATUS_INACTIVE] . '</span>';
            } else {
                $item->customStatus = '<span class="badge bg-secondary rounded-3" style="padding: 5px 10px">' . Store::STATUS_TEXTS[Store::STATUS_INACTIVE] . '</span>';
            }
            $item->locality = $item->organization?->name;
            $item->tdv      = '';
            if ($item->organization?->users) {
                foreach ($item->organization->users as $user) {
                    $item->tdv .= '<span class="badge badge-light-primary" style="padding: 5px 10px; margin: 3px 2px">' . $user?->name . '</span>';
                }
            }
            $fullAddress    = $item->address;
            $fullAddress    .= $item?->ward?->ward_name ? ' - ' . $item?->ward?->ward_name : '';
            $fullAddress    .= $item?->district?->district_name ? ' - ' . $item?->district?->district_name : '';
            $fullAddress    .= $item?->province?->province_name ? ' - ' . $item?->province?->province_name : '';
            $item->address  = $fullAddress;
            $item->features = "";
            if ($canView) {
                $item->features .= '<a class="btn btn-sm btn-icon"
                   href="' . route('admin.stores.show', $item->id) . '">
                    <i data-feather="file-text" class="font-medium-2 text-body"></i>
                </a>';
            }
            if ($canEdit) {
                $item->features .= '<a class="btn btn-sm btn-icon"
                   href="' . route('admin.stores.edit', $item->id) . '">
                    <i data-feather="edit" class="font-medium-2 text-body"></i>
                </a>';
            }

            return $item;
        });
        $nameTable = Helper::userCan('xem_nha_thuoc') || Helper::userCan('tdv_xem_nha_thuoc') ? 'store-list' : '';

        return new TableHelper(
            collections: $results,
            nameTable: $nameTable,
        );
    }

    public function getTableForTdv($requestParams = [], $showOption = [])
    {
        $showOption    = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [
                "column" => "stores.created_at",
                "type"   => "DESC"
            ]
        ], $showOption);
        $requestParams = $this->getRequestParams($requestParams);
        $stores        = $this->repository->getByRequestTDV(
            with: ['storeRank', 'storeRank.productGroup', 'province', 'district', 'ward', 'reportRevenueStores'],
            requestParams: $requestParams,
            showOption: $showOption
        );

        $stores->map(function ($store) use ($requestParams) {
            $ranks                     = [];
            $groups                    = []; // Ds group product
            $periodOption              = []; // Chu ky
            $typeGroup                 = [];
            $doanhThuProductTypePeriod = [];
            $chuKyProductType          = [];
            $chuKyGroup                = [];
            $store->doanhthu           = null;

            if ($store->reportRevenueStores->count()) {
                $store->doanhthu = $store->reportRevenueStores->sum('total_amount');
            }
            if (isset($requestParams['dateRange'])) {
                $listProductType = $this->reportRevenueStoreRankService->getProductTypesFromStoreType($store->type);
                $groups          = $this->productGroupRepository->getGroupByArrType($listProductType);
//                foreach ($groups as $item) {
//                    $chuKyGroup[]
//
//                }

                // Lay chu ky theo tung loai san pham
                foreach ($listProductType as $productType) {
                    $periodOfYear               = ProductGroup::PRODUCT_TYPES[$productType]['period_of_year'];
                    $periodOption[$productType] = collect(Helper::periodOptions($requestParams['year'], $periodOfYear))
                        ->filter(function ($item) {
                            return $item['ended_at_timestamp'] < now()->getTimestamp();
                        })
                        ->sortByDesc('period')
                        ->first();
                }

                // Voi thang/nam tim kiem thi tuong ung thoi gian bat dau va ket thuc cua moi chu ky theo loai san pham
                // Doanh thu theo product type
                foreach ($periodOption as $productType => $item) {
                    if ($item) {
                        $_year                                   = $requestParams['year'];
                        $_month                                  = $requestParams['month'];
                        $_period                                 = Helper::getPeriodByDateAndProductType($_year . '-' . $_month . '-01', $productType);
                        $doanhThuProductTypePeriod[$productType] = $store->storeRank
                            ?->where('from_date', '<=', $_period['started_at'])
                            ?->where('to_date', '>=', $_period['ended_at'])
                            ->where('group_id', 1)
                            ->sum('revenue');
                        $chuKyProductType[$productType]          = $item['name'];
                    }
                }
//                dd($doanhThuProductTypePeriod);

                $groups->map(function ($productGroup) use ($doanhThuProductTypePeriod, $store, $chuKyProductType) {
                    $productGroup->doanhthu_group    = $doanhThuProductTypePeriod[$productGroup->product_type] ?? null;
                    $productGroup->product_type_name = ProductGroup::PRODUCT_TYPES[$productGroup['product_type']]['text'] ?
                        'Loại ' . ProductGroup::PRODUCT_TYPES[$productGroup['product_type']]['text'] : null;
                    $productGroup->chuky             = $chuKyProductType[$productGroup->product_type] ?? null;
                });

                $storeRanks = $store->storeRank
                    ?->where('from_date', '<=', $requestParams['dateRange']['from'])
                    ?->where('to_date', '>=', $requestParams['dateRange']['to']);
                foreach ($storeRanks ?? [] as $rank) {
                    $productGroupName         = $rank->productGroup->name;
                    $ranks[$productGroupName] = $productGroupName . ": " . $rank->rank;
                }

                $ranks = implode(', ', $ranks);
            }

            $routeOrderList    = route('admin.tdv.store.turnover', [
                'storeId' => $store->id,
                'search'  => [
                    'month' => $requestParams['month'] ?? now()->month,
                    'year'  => $requestParams['year'] ?? now()->year
                ],
            ]);
            $routeStoreDetail  = route('admin.tdv.store.show', ['id' => $store->id]);
            $routeStoreEdit    = route('admin.tdv.store.edit', ['id' => $store->id]);
            $storeCode         = $store->code;
            $storeName         = $store->name;
            $addressInfo       = $store->getFullAddressAttribute();
            $store->store_info = "<div style='margin-top: 0.5rem;margin-bottom: 0.5rem;'>
                <div class='d-flex'>
                    <span class='fw-bolder'>
                        <a href='" . $routeStoreDetail . "'>" . $storeName . "</a>
                    </span>
                    <span class='ms-auto fst-italic'>
                        <a href='" . $routeStoreDetail . "'>" . $storeCode . "</a>
                    </span>
                </div>";
            $store->store_info .= $ranks ? "<b>Hạng: </b>" . $ranks . "<br>" : "";
            $store->store_info .= "<b>ĐC: </b>" . $addressInfo . "<br>";

            foreach ($groups as $item) {
                $groupAmount = Helper::formatPrice($item->doanhthu_group);
                if ($groupAmount) {
                    $store->store_info .= "<b>" . $item->name . " - " . $item->chuky . ":</b > " . $groupAmount . " </br>";
                }
            }

            if ($store->doanhthu) {
                $store->store_info .= " <div><a href = '" . $routeOrderList . "' class='float-end badge bg-secondary' > Doanh thu </a ></div> ";
            }
            $store->store_info .= "<div><a href = '" . $routeStoreEdit . "' class='float-end badge bg-secondary mx-1' > Sửa NT </a ></div> ";
            $store->store_info .= '</div>';

            return $store;
        });

        return new TableHelper(
            collections: $stores,
            nameTable: 'tdv-store-list',
        );
    }

    public function indexOptions(): array
    {
        $current_user        = Helper::currentUser();
        $user_organization   = Helper::getUserOrganization();
        $search_division_id  = request('search.division_id');
        $userRoleName        = $current_user?->roles?->first()->name;
        $show_division_field = $show_locality_field = true;
        $divisionActiveTypes = [];
        $show_tdv_field      = false;
        $count_division      = count($user_organization[Organization::TYPE_KHU_VUC] ?? []);
        $count_locality      = count($user_organization[Organization::TYPE_DIA_BAN] ?? []);
        if ($count_division == 1) {
            $show_division_field = false;
            $search_division_id  = array_shift($user_organization[Organization::TYPE_KHU_VUC]);
        }
        if ($count_locality == 1) {
            $show_locality_field = false;
        }
        if ($userRoleName == User::ROLE_Admin) {
            $divisionActiveTypes = [
                Organization::TYPE_TONG_CONG_TY,
                Organization::TYPE_CONG_TY,
                Organization::TYPE_MIEN,
                Organization::TYPE_KHAC,
                Organization::TYPE_KHU_VUC
            ];
        }
        if ($userRoleName == User::ROLE_SALE_ADMIN) {
            $divisionActiveTypes = [
                Organization::TYPE_MIEN,
                Organization::TYPE_KHU_VUC
            ];
        }
        if ($userRoleName == User::ROLE_TDV) {
            $show_tdv_field      = false;
            $divisionActiveTypes = [
                Organization::TYPE_KHU_VUC
            ];
        }

        $requestName     = request('search.name') ?? null;
        $requestDivision = request('search.division_id') ?? null;
        $locality_id     = request('search.locality_ids') ?? null;
        $requestCreated  = request('search.created_by') ?? null;
        $requestStatus   = request('search.status') ?? null;
        $currentWeekday  = now()->weekday();
        $currentWeekday  = $currentWeekday === 0 ? 1 : $currentWeekday;
        $requestWeekday  = request('search.weekday') ?? $currentWeekday;
        if ($requestName) {
            $locality_id     = null;
            $requestDivision = null;
            $requestCreated  = null;
            $requestStatus   = null;
            $requestWeekday  = null;
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
                    'activeTypes'     => $divisionActiveTypes,
                    'excludeTypes'    => [
                        Organization::TYPE_DIA_BAN,
                    ],
                    'hasRelationship' => true,
                    'setup'           => [
                        'multiple'    => true,
                        'name'        => 'search[division_id][]',
                        'class'       => '',
                        'id'          => 'division_id',
                        'attributes'  => '',
                        'selected'    => $requestDivision,
                        'placeholder' => '- Khu vực -',
                    ],
                    'widthDefault'    => 'auto',
                ],
            ];
        }

        if ($show_locality_field) {
            $localityOptions = $search_division_id ? $this->organizationRepository->getLocalityByDivision($search_division_id)->pluck('name', 'id')->toArray() : [];
            $searchOptions[] = [
                'wrapClass'     => 'col-md-2',
                'type'          => 'selection',
                'name'          => 'search[locality_ids]',
                'defaultValue'  => $locality_id,
                'id'            => 'form-locality_id',
                'options'       => ['' => '- Địa bàn -'] + $localityOptions,
                'other_options' => ['option_class' => 'ajax-locality-option'],
            ];
        }
        $option_tdv = ['' => '- TDV -'];
        if ($show_tdv_field) {
            if ($locality_id) {
                $option_tdv += $this->organizationRepository->getUserByLocality($locality_id)
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
        if ($userRoleName != User::ROLE_TDV) {
            $searchOptions[] = [
                'wrapClass'    => 'col-md-2',
                'type'         => 'selection',
                'name'         => 'search[status]',
                'defaultValue' => $requestStatus,
                'id'           => 'form-status',
                'options'      => [
                    ''                          => '- Trạng thái -',
                    Store::STATUS_ACTIVE_TEXT   => Store::STATUS_TEXTS[Store::STATUS_ACTIVE],
                    Store::STATUS_INACTIVE_TEXT => Store::STATUS_TEXTS[Store::STATUS_INACTIVE],
                ],
            ];
        }
        if ($userRoleName == User::ROLE_TDV) {
            $searchOptions[] = [
                'wrapClass'    => 'col-md-2',
                "type"         => "selection",
                "name"         => "search[weekday]",
                "defaultValue" => $requestWeekday,
                "id"           => "form - weekday",
                "options"      => ['' => '- Thứ -', 'all' => 'Tất cả'] + Line::WEEKDAYS ?? [],
            ];
        }

        return compact('searchOptions');
    }

    public function checkStoreExist(array $attributes = [])
    {
        $existStore = $this->repository->checkStoreExist(['province', 'district', 'ward', 'organization'], $attributes);
        return $this->highlightDuplicateInfoStore($existStore, $attributes);
    }

    public function highlightDuplicateInfoStore($collectionStore = null, array $search = [])
    {
        return $collectionStore->map(function ($_store) use ($search) {
            $address     = ($_store->address == $search['address'] && $search['address'] != '') ?
                'Địa chỉ: <span class="text - danger">' . $_store->address . '</span>' : $_store->address;
            $addressInfo = [
                $address,
                $_store?->ward?->ward_name,
                $_store?->district?->district_name,
                $_store?->province?->province_name,
            ];

            $html = ($_store->name == $search['name'] && $search['name'] != '') ?
                'Tên NT: <span class="text - danger">' . $_store->name . '</span>' :
                'Tên NT: ' . $_store->name;
            $html .= $_store->code != "" ? ' (' . $_store->code . ')' : '';

            $html .= '<div>Đc: ';
            $html .= implode(' - ', $addressInfo);
            $html .= $_store?->organization?->name != "" ? ' - DB: ' . $_store?->organization?->name : "";
            $html .= '</div>';

            if ($_store->phone_owner == $search['phone_owner'] && $search['phone_owner'] != '') {
                $html .= '<div class="mb - 1">SĐT NT: <span class="text - danger pe - 1">' . $_store->phone_owner . '</span></div>';
            }

            if ($_store->vat_number == $search['vat_number'] && $search['vat_number'] != '') {
                $html .= '<div class="mb - 1">MST: <span class="text - danger pe - 1">' . $_store->vat_number . '</span></div>';
            }

            return $html;
        });
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
        $search_division           = $requestParams['division_id'] ?? null;
        $search_locality           = $requestParams['locality_ids'] ?? null;

        if (!$search_division && !$search_locality) {
            $requestParams['locality_ids'] = $userLocalities;
        } elseif (!$search_division && $search_locality) {
            $requestParams['locality_ids'] = array($search_locality);
        } elseif ($search_division && !$search_locality) {
            $requestParams['locality_ids'] = $this->organizationRepository->getLocalityByArrOrganization($search_division)->pluck('id')->toArray();
        } elseif ($search_division && $search_locality) {
            $requestParams['locality_ids'] = array($search_locality);
        }
        if ($roleName == User::ROLE_TDV) {
            if (request()->route()->getName() != 'admin.tdv.store.index') {
                $requestParams['created_by'] = $currentUser->id;
            } else {
                $requestParams['status'] = $requestParams['status'] ?? Store::STATUS_ACTIVE_TEXT;
            }

            $requestParams['dateRange'] = [
                'from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                'to'   => Carbon::now()->endOfMonth()->format('Y-m-d')
            ];

            if (isset($requestParams['month'])
                && isset($requestParams['year'])
                && request()->route()->getName() == 'admin.tdv.store.index'
            ) {
                $monthText                  = str_pad($requestParams['month'], 2, "0", STR_PAD_LEFT);
                $requestParams['dateRange'] = [
                    'from' => $requestParams['year'] . "-" . $monthText . "-01",
                    'to'   => $requestParams['year'] . "-" . $monthText . "-31",
                ];
            }

            if (isset($requestParams['name'])) {
                $requestParams = $this->setNullRequestSearch($requestParams);
            }
        }
        $requestParams['weekday'] = $requestParams['weekday'] ?? now()->weekday();
        $requestParams['month']   = $requestParams['month'] ?? Carbon::now()->month;
        $requestParams['year']    = $requestParams['year'] ?? Carbon::now()->year;

        return $requestParams;
    }

    public function getFormOptions($model)
    {
        $options                    = parent::formOptions($model);
        $default_values             = $options['default_values'];
        $currentUser                = Helper::currentUser();
        $organizationOfCurrentUser  = Helper::getUserOrganization();
        $options['status']          = Store::STATUS_TEXTS;
        $options['roleName']        = $currentUser?->roles[0]?->name;
        $options['provinces']       = Province::query()->get() ?? null;
        $options['localities']      = $this->organizationRepository->getLocalityActive();
        $options['canAddEditStore'] = $currentUser->canany(['sua_nha_thuoc', 'them_nha_thuoc']);
        $userLocalityIds            = $organizationOfCurrentUser[Organization::TYPE_DIA_BAN] ?? [];
        $userProvinces              = $this->organizationRepository->getProvinceByLocalities($userLocalityIds) ?? [];
        $currentRoute               = request()->route()->getName();

        if (!empty($userProvinces)) {
            $tmpProvinceId = [];
            foreach ($userProvinces as $item) {
                $tmpProvinceId[] = $item['province_id'] ?? null;
            }
            $options['userProvinces'] = $options['provinces']->filter(function ($item) use ($tmpProvinceId) {
                return in_array($item->id, $tmpProvinceId);
            });
        } else {
            $options['userProvinces'] = $options['provinces'];
        }

        if (
            $currentRoute == 'admin.stores.edit' ||
            $currentRoute == 'admin.stores.show' ||
            $currentRoute == 'admin.tdv.store.edit' ||
            $currentRoute == 'admin.tdv.store.show'
        ) {
            $storeId                     = $model->id;
            $arrFileId                   = $default_values['file_id'] ? json_decode($default_values['file_id'], true) : [];
            $options['files']            = $this->fileRepository->getByArrId($arrFileId);
            $options['parent_store']     = $this->repository->getParentStore($default_values['parent_id']) ?? null;
            $options['parent_code_name'] = $options['parent_store'] ? $options['parent_store']['code'] . ' - ' . $options['parent_store']['name'] : null;
            $province                    = $this->provinceRepository->find($default_values['province_id'] ?? 0) ?? null;
            $district                    = $this->districtRepository->find($default_values['district_id'] ?? 0) ?? null;
            $options['userDistricts']    = $province?->district()?->get() ?? null;
            $options['userWards']        = $district?->ward()?->get() ?? null;
            if ($province) {
                $options['userLocalities'] = $this->organizationRepository->getLocalityByProvince($province->id);
            }
            $localityId                                = $default_values['organization_id'] ?? null;
            $options['localityLines']                  = $this->lineRepository->getByLocality($localityId);
            $options['lineStore']                      = $this->lineStoreService->getLineStoreActive(null, $storeId)->first();
            $options['default_values']['line']         = $options['lineStore']->line_id ?? null;
            $options['default_values']['number_visit'] = $options['lineStore']->number_visit ?? LineStore::DEFAULT_NUMBER_VISIT;
            $options['tdv']                            = $this->organizationRepository->getUserByLocality($localityId) ?? collect();
            $options['default_values']['localityName'] = $options['localities']->firstWhere('id', $default_values['organization_id'] ?? '')?->name;
            $options['default_values']['storeId']      = $storeId;
        }

        if (old('province_id')) {
            $province                  = $this->provinceRepository->find(old('province_id')) ?? null;
            $options['userDistricts']  = $province->district()->get() ?? null;
            $options['userLocalities'] = $this->organizationRepository->getLocalityByProvince(old('province_id'));
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

    /**
     * @param array $attributes
     * @return array
     */
    public function trimValue(array $attributes = []): array
    {
        $results  = [];
        $arrAllow = ['array', null, ''];
        foreach ($attributes as $key => $atr) {
            if ($atr && !in_array(gettype($atr), $arrAllow)) {
                $results[$key] = trim($atr);
            } else {
                $results[$key] = $atr;
            }
        }
        return $results;
    }

    /**
     * @param array $oldData
     * @param array $newData
     * @return bool
     * return true if $oldData == $newData
     */
    public function compareData(array $oldData = [], array $newData = []): bool
    {
        $flag       = true;
        $updateData = [
            'type'            => $newData['type'] ?? null,
            'name'            => $newData['name'] ?? null,
            'province_id'     => $newData['province_id'] ?? null,
            'district_id'     => $newData['district_id'] ?? null,
            'ward_id'         => $newData['ward_id'] ?? null,
            'organization_id' => $newData['organization_id'] ?? null,
            "address"         => $newData['address'] ?? null,
            "lat"             => $newData['lat'] ?? null,
            "lng"             => $newData['lng'] ?? null,
            "phone_owner"     => $newData['phone_owner'] ?? null,
            "phone_web"       => $newData['phone_web'] ?? null,
            "parent_id"       => $newData['parent_id'] ?? null,
            "vat_buyer"       => $newData['vat_buyer'] ?? null,
            "vat_company"     => $newData['vat_company'] ?? null,
            "vat_number"      => $newData['vat_number'] ?? null,
            "vat_email"       => $newData['vat_email'] ?? null,
            "vat_address"     => $newData['vat_address'] ?? null,
            "note_private"    => $newData['note_private'] ?? null,
            "file_id"         => $newData['file_id'] ?? null,
            "vat_parent"      => $newData['vat_parent'] ?? null,
            "code"            => $newData['code'] ?? null,
            "status"          => $newData['status'] ?? null,
        ];

        foreach ($updateData as $key => $value) {
            if (isset($oldData[$key]) && $oldData[$key] != $value) {
                $flag = false;
            }
        }

        return $flag;
    }

    public function checkLocalityPermission($storeModel = null, $currentUser = null): bool
    {
        $currentUser               = $currentUser ?? Helper::currentUser();
        $organizationOfCurrentUser = Helper::getUserOrganization();
        $roleName                  = $currentUser?->roles[0]?->name;
        $userLocalities            = $organizationOfCurrentUser[Organization::TYPE_DIA_BAN] ?? [];

        if (!$storeModel) return false;
        if ($roleName == User::ROLE_Admin) return true;

        $storeModel->load('organization');
        $localityStore = $storeModel->organization->id ?? null;

        if (!count($userLocalities) || !in_array($localityStore, $userLocalities)) {
            return false;
        }

        return true;
    }

    public function urlRedirect(): string
    {
        return Helper::userRoleName() == User::ROLE_TDV ? 'admin.tdv.store.index' : 'admin.stores.index';
    }

    public function setNullRequestSearch($requestParams = array())
    {
        $requestParams['locality_ids']         = null;
        $requestParams['division_id']          = null;
        $requestParams['weekday']              = null;
        $requestParams['month']                = null;
        $requestParams['year']                 = null;
        $requestParams['number_day_not_order'] = null;
        $requestParams['not_enough_visit']     = null;
        $requestParams['dateRange']            = null;

        return $requestParams;
    }
}
