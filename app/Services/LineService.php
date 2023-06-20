<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Helpers\TableHelper;
use App\Models\LineStore;
use App\Models\Organization;
use App\Models\ProductGroup;
use App\Models\ProductLog;
use App\Models\User;
use App\Repositories\Checkin\CheckinRepositoryInterface;
use App\Repositories\LineStore\LineStoreRepositoryInterface;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\LineGroup\LineGroupRepositoryInterface;
use App\Repositories\ReportRevenueOrder\ReportRevenueOrderRepository;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Repositories\ProductGroup\ProductGroupRepositoryInterface;
use App\Repositories\StoreOrder\StoreOrderRepositoryInterface;
use App\Repositories\Line\LineRepositoryInterface;
use App\Services\LineStoreService;
use App\Models\Line;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LineService extends BaseService
{
    protected LineRepositoryInterface $repository;
    protected OrganizationRepositoryInterface $organizationRepository;
    protected LineGroupRepositoryInterface $lineGroupRepository;
    protected ProductGroupRepositoryInterface $productGroupRepository;
    protected StoreRepositoryInterface $storeRepository;
    protected StoreOrderRepositoryInterface $storeOrderRepository;
    protected LineStoreRepositoryInterface $lineStoreRepository;
    protected CheckinRepositoryInterface $checkinRepository;
    protected ReportRevenueOrderRepository $reportRevenueOrderRepository;
    protected LineStoreService $lineStoreService;

    public function __construct(
        LineRepositoryInterface         $repository,
        OrganizationRepositoryInterface $organizationRepository,
        StoreRepositoryInterface        $storeRepository,
        ProductGroupRepositoryInterface $productGroupRepository,
        StoreOrderRepositoryInterface   $storeOrderRepository,
        LineStoreRepositoryInterface    $lineStoreRepository,
        LineGroupRepositoryInterface    $lineGroupRepository,
        CheckinRepositoryInterface      $checkinRepository,
        ReportRevenueOrderRepository    $reportRevenueOrderRepository,
        LineStoreService                $lineStoreService,
    )
    {
        parent::__construct();

        $this->repository                   = $repository;
        $this->organizationRepository       = $organizationRepository;
        $this->lineGroupRepository          = $lineGroupRepository;
        $this->productGroupRepository       = $productGroupRepository;
        $this->storeRepository              = $storeRepository;
        $this->storeOrderRepository         = $storeOrderRepository;
        $this->lineStoreRepository          = $lineStoreRepository;
        $this->checkinRepository            = $checkinRepository;
        $this->reportRevenueOrderRepository = $reportRevenueOrderRepository;
        $this->lineStoreService             = $lineStoreService;
    }

    public function setModel()
    {
        return new Line();
    }

    public function formOptions($model = null, $id = null): array
    {
        $options                      = $this->repository->formOptions($model);
        $currentUser                  = Helper::currentUser();
        $currentRoute                 = request()->route()->getName();
        $options['userOrganizations'] = Helper::getUserOrganization($currentUser);
        $options['roleName']          = $currentUser?->roles[0]?->name;
        $options['productGroups']     = $this->productGroupRepository->getRootGroup();
        $options['isDisabled']        = $options['default_values']['status'] == Line::STATUS_INACTIVE ? 'disabled' : '';
        $dayOfWeek                    = Line::WEEKDAYS;
        $options['productGroupDays']  = $options['productGroups']->map(function ($item) use ($dayOfWeek) {
            $item['days'] = $dayOfWeek;
            return $item;
        });
        $storeInLineStore             = [];

        if ($currentRoute == 'admin.lines.edit') {
            $line                          = $this->repository->find($id, ['storesRunning', 'lineGroup']);
            $options['storesRunning']      = $line->storesRunning ?? null;
            $lineGroups                    = $line->lineGroup ?? [];
            $options['localityFreeStores'] = $this->storeRepository->getByLocality($options['default_values']['organization_id'] ?? null);
            $arrLineGroup                  = [];
            foreach ($lineGroups as $lineGroup) {
                $arrLineGroup[] = $lineGroup->group_id . '-' . $lineGroup->day_of_week;
            }
        }

        if (old('day_of_week')) $arrLineGroup = old('day_of_week');
        if (old('stores')) $options['storesRunning'] = $this->storeRepository->getByArrId(old('stores')) ?? null;
        if (old('locality')) {
            $selectedStore                                = [];
            $selectedStore                                = old('stores') ? $selectedStore + old('stores') : [];
            $selectedStore                                = old('free-store') ? $selectedStore + old('free-store') : [];
            $options['default_values']['organization_id'] = old('locality');
            $options['localityFreeStores']                = $this->storeRepository->getByLocality(old('locality') ?? null)->whereNotIn('id', $selectedStore);
        }
        $options['lineGroups'] = $arrLineGroup ?? [];

        return $options;
    }

    // SA, Admin them store khi tao moi tuyen
    public function create(array $attributes = [])
    {
        $currentUser            = Helper::currentUser();
        $arr                    = [];
        $arr['name']            = $attributes['name'] ?? null;
        $arr['organization_id'] = $attributes['locality'] ?? null;
        $arr['created_by']      = $currentUser->id;
        $arr['status']          = Line::STATUS_ACTIVE;
        $line                   = $this->repository->create($arr);

        if ($line) {
            $lineId = $line->id;
            if (isset($attributes['day_of_week']) && count($attributes['day_of_week'])) {
                $this->createLineGroup($lineId, $attributes['day_of_week'] ?? null);
            }
            if (isset($attributes['stores']) && count($attributes['stores'])) {
                foreach ($attributes['stores'] as $store) {
                    $this->lineStoreRepository->processCreate([
                        'line_id'        => $lineId,
                        'store_id'       => $store,
                        'from'           => now()->toDateString(),
                        'to'             => null,
                        'number_visit'   => LineStore::DEFAULT_NUMBER_VISIT,
                        'reference_type' => LineStore::REFERENCE_TYPE_LINE,
                        'created_by'     => $currentUser->id,
                        'updated_by'     => null,
                        'status'         => LineStore::STATUS_ACTIVE,
                        'created_at'     => now()->toDateTimeString(),
                        'updated_at'     => now()->toDateTimeString(),
                    ]);
                }
            }
        }
    }

    public function update($id, array $attributes = [])
    {
        $oldLine = $this->repository->find($id, ['lineGroup', 'stores']);
        if ($oldLine) {
            $oldLocality  = $oldLine->organization_id;
            $oldAllStore  = $oldLine->stores;
            $oldDayOfWeek = $oldLine?->lineGroup->pluck('day_of_week')->toArray() ?? [];
            $oldStores    = $this->lineStoreService->getLineStoreActive($oldLine->id)->pluck('store_id')->toArray() ?? null;
            $newLocality  = $attributes['locality'] ?? null;
            $newDayOfWeek = [];

            if (isset($attributes['day_of_week'])) {
                foreach ($attributes['day_of_week'] as $dayOfWeek) {
                    $tmp = explode('-', $dayOfWeek);
                    if (isset($tmp[1])) {
                        $newDayOfWeek[] = intval($tmp[1]);
                    }
                }
            }

            if (($oldLocality != $newLocality || $oldDayOfWeek != $newDayOfWeek) && !$oldAllStore->count()) {
                // Cap nhat lai line
                $this->repository->update($id, [
                    'name'            => $attributes['name'] ?? '',
                    'organization_id' => $newLocality ?? '',
                    'updated_by'      => Auth::user()->id ?? '',
                ]);

                // Cap nhat lai line_group
                $this->createLineGroup($id, $attributes['day_of_week']);
            } else {
                if ($oldLocality != $newLocality || $oldDayOfWeek != $newDayOfWeek) {
                    $this->lineStoreService->closeStoresInLine($oldStores, $oldLine->id); // Dong cac nha thuoc thuoc tuyen cu
                    $oldLine->update(["updated_by" => Auth::user()->id, "status" => Line::STATUS_INACTIVE]); // Dong tuyen cu
                    $this->create($attributes); // Tao tuyen moi
                } else {
                    $arr               = [];
                    $arr['name']       = $attributes['name'] ?? null;
                    $arr['updated_by'] = Auth::user()->id;
                    $line              = $this->repository->update($id, $arr);
                }
            }

            // Tao line_store
            if (isset($attributes['stores'])) {
                DB::transaction(function () use ($oldStores, $id, $attributes) {
                    $newStores = $attributes['stores'] ?? [];
                    $this->compareUpdateLineStore($oldStores, $newStores, $id);
                });
            }
        }
    }

    public function compareUpdateLineStore($oldStores = [], $newStores = [], $lineId = null)
    {
        $arrStoreClose  = array_diff($oldStores, $newStores); // Lay ra store da bi bo di
        $arrStoreCreate = array_diff($newStores, $oldStores); // Lay ra store duoc them moi vao

        if (count($arrStoreClose)) {
            $this->lineStoreService->closeStoresInLine($arrStoreClose, $lineId);
        }

        if (count($arrStoreCreate)) {
            foreach ($arrStoreCreate as $item) {
                $this->lineStoreService->create([
                    'line_id'        => $lineId,
                    'store_id'       => $item,
                    'reference_type' => LineStore::REFERENCE_TYPE_LINE,
                    'status'         => LineStore::STATUS_ACTIVE,
                ]);
            }
        }
    }

    public function createLineGroup($lineId = null, array $dayOfWeek = [])
    {
        $line         = $this->repository->find($lineId, ['lineGroup']);
        $lineGroupIds = $line->lineGroup->pluck('id')->toArray();
        $this->lineGroupRepository->deleteMultiple($lineGroupIds);

        if ($lineId && !empty($dayOfWeek)) {
            foreach ($dayOfWeek as $day) {
                $tmp = explode('-', $day);
                $this->lineGroupRepository->create([
                    'line_id'     => $lineId,
                    'group_id'    => $tmp[0],
                    'day_of_week' => $tmp[1],
                ]);
            }
        }
    }

    public function indexOptions(): array
    {
        $current_user        = Helper::currentUser();
        $user_organization   = Helper::getUserOrganization();
        $search_division_id  = request('search.division_id');
        $show_division_field = $show_locality_field = true;
        $show_tdv_field      = true;
        $count_division      = count($user_organization[Organization::TYPE_KHU_VUC] ?? []);
        $count_locality      = count($user_organization[Organization::TYPE_DIA_BAN] ?? []);
        $arrYear             = Helper::getRangeYear(Line::STARTYEAR, now()->format('Y'));

        if ($count_division == 1) {
            $show_division_field = false;
            $search_division_id  = array_shift($user_organization[Organization::TYPE_KHU_VUC]);
        }
        if ($count_locality == 1) {
            $show_locality_field = false;
        }
        if ($current_user?->roles?->first()->name == User::ROLE_TDV) {
            $show_tdv_field = false;
        }

        $defaultYear    = request('search.year') ?? intval(now()->format('Y'));
        $defaultMonth   = request('search.month') ?? intval(now()->format('m'));
        $defaultWeekday = in_array('weekday', array_keys(request('search', [])))
            ? request('search.weekday')
            : Helper::getWeekdayNumber();

        $searchOptions   = [];
        $searchOptions[] = [
            'wrapClass'    => 'col-md-2',
            'type'         => 'text',
            'name'         => 'search[name]',
            'placeholder'  => 'Tên tuyến',
            'id'           => 'form_search_name',
            'defaultValue' => request('search.name'),
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
                        'selected'   => request('search.division_id'),
                    ],
                    'widthDefault'    => 'auto',
                ],
            ];
        }

        $locality_id = request('search.locality_ids');

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
                'name'          => 'search[user_id]',
                'defaultValue'  => request('search.user_id'),
                'id'            => 'form-user_id',
                'options'       => $option_tdv,
                'other_options' => ['option_class' => 'ajax-tdv-option'],
            ];
        }

        $searchOptions[] = [
            'wrapClass'    => 'col-md-2',
            'type'         => 'selection',
            'name'         => 'search[status]',
            'defaultValue' => request('search.status'),
            'id'           => 'form-status',
            'options'      => [
                    '' => '- Trạng thái -',
                ] + Line::STATUS_TEXTS,
        ];

        $searchOptions[] = [
            'type'                => 'selectionYearMonthWeek',
            'id'                  => 'form-year-month-week',
            'class'               => 'col-md-6',
            'yearMonthWeekConfig' => [
                [
                    'type'         => 'selection',
                    'name'         => 'search[year]',
                    'defaultValue' => $defaultYear,
                    'id'           => 'form-year',
                    'class'        => 'col-md-4',
                    'options'      => [
                            '' => '- Năm -',
                        ] + $arrYear,
                ],
                [
                    'type'         => 'selection',
                    'name'         => 'search[month]',
                    'defaultValue' => $defaultMonth,
                    'id'           => 'form-month',
                    'class'        => 'col-md-4',
                    'options'      => [
                            '' => '- Tháng -',
                        ] + Line::MONTHS,
                ],
                [
                    'type'         => 'selection',
                    'name'         => 'search[weekday]',
                    'defaultValue' => $defaultWeekday,
                    'id'           => 'form-weekday',
                    'class'        => 'col-md-4',
                    'options'      => [
                            ''                    => '- Thứ -',
                        ] + Line::WEEKDAYS,
                ]
            ],
        ];

        $searchOptions[] = [
            'type'         => 'checkbox',
            'name'         => 'search[emptyLine]',
            'defaultValue' => request('search.emptyLine'),
            'id'           => 'form-empty-line',
            'placeholder'  => 'Tuyến trống'
        ];

        return compact('searchOptions');
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
            $requestParams['locality_ids'] = $this->organizationRepository->getLocalityByDivision($search_division)->pluck('id')->toArray();
        } elseif ($search_division && $search_locality) {
            $requestParams['locality_ids'] = array($search_locality);
        }
        if ($roleName == User::ROLE_TDV && request()->route()->getName() != 'admin.tdv.store.index') {
            $requestParams['user_id'] = $currentUser->id;
        }

        $requestParams['name']    = $requestParams['name'] ?? null;
        $requestParams['user_id'] = $requestParams['user_id'] ?? null;
        $requestParams['status']  = $requestParams['status'] ?? null;
        $requestParams['year']    = $requestParams['year'] ?? now()->format('Y');
        $requestParams['month']   = $requestParams['month'] ?? now()->format('m');

        if (isset($requestParams['weekday'])) {
            if ($requestParams['weekday'] == Line::ALL_DAY_OF_WEEK) {
                $requestParams['weekday'] = array_keys(Line::WEEKDAYS);
            } else {
                $requestParams['weekday'] = (array)$requestParams['weekday'];
            }
        } else {
            $requestParams['weekday'] = (array)now()->dayOfWeek;
        }

        return $requestParams;
    }

    public function getWeekdayName(array $weekday = [])
    {
        $arrName = [];
        foreach ($weekday as $item) {
            if (Line::WEEKDAYS[intval($item)]) $arrName[] = Line::WEEKDAYS[intval($item)];
        }

        return $arrName;
    }

//    public function storeAppendData(
//        $stores = null,
//        $userCheckin = null,
//        $latestCheckin = null,
//        $storeLatestBooking = null,
//        $lineStatus = null,
//        $arrStoreRunningIds = null
//    )
//    {
//        $showStores = collect();
//        $stores->map(function ($store) use ($userCheckin, $latestCheckin, $storeLatestBooking) {
//            $store->numCheckin    = $userCheckin[$store->id] ?? 0;
//            $store->latestCheckin = $latestCheckin->checkin_at ?? null;
//            $store->latestBooking = $storeLatestBooking[$store->id] ?? null;
//            $store->numberVisit   = $store->pivot->number_visit ?? null;
//            return $store;
//        });
//
//        if ($lineStatus == Line::STATUS_ACTIVE) {
//            foreach ($stores as $_store) {
//                if ($_store->pivot->status == LineStore::STATUS_ACTIVE && in_array($_store->id, $arrStoreRunningIds)) {
//                    $showStores->push($_store);
//                }
//            }
//        } else {
//            $showStores = $stores;
//        }
//
//        return $showStores;
//    }

    public function getTable($requestParams = [], $showOption = [])
    {
        $requestParams  = $this->getRequestParams($requestParams);
        $currentWeekday = Carbon::now()->format('N');
        $requestWeekday = $requestParams['weekday'] ?? (array)$currentWeekday;
        $lineStoreUser  = [];
        $lines          = $this->repository->getByRequest(
            with: ['organizations', 'organizations.users', 'organizations.users.checkin', 'stores', 'storesRunning', 'productGroup'],
            requestParams: $requestParams,
            showOption: $showOption,
        );

        $objLines = [];
        // Loop tuyen tim xem tuyen nao duoc hien thi theo dieu kien tim kiem thu trong tuan
        foreach ($lines as $line) {
            $lineWeekDay = [];
            foreach ($line->productGroup as $pGroup) {
                if (isset($pGroup->pivot->day_of_week) && in_array($pGroup->pivot->day_of_week, $requestWeekday)) {
                    $lineWeekDay[] = $pGroup->pivot->day_of_week;
                }
            }
            sort($lineWeekDay);
            $lineWeekDay         = array_unique($lineWeekDay);
            $line->day_of_week   = $lineWeekDay;
            $objLines[$line->id] = $line;
            $line->orgTDV        = $line?->organizations?->users;

            if (isset($requestParams['user_id'])) {
                $line->findUserTDV = $line->orgTDV->filter(function ($user) use ($requestParams) {
                    return $requestParams['user_id'] == $user->id;
                });
            } else {
                $line->findUserTDV = $line->orgTDV;
            }
        }

        foreach ($objLines as $line
        ) {
            if (!isset($lineStoreUser[$line->organization_id])) {
                $lineStoreUser[$line->organization_id] = [
                    'rowspan' => 0,
                    'items'   => [],
                ];
            }

            $organizationName   = $line?->organizations?->name;
            $organizationId     = $line->organization_id;
            $lineName           = $line->name;
            $stores             = $line->stores ?? null;
            $storesRunning      = $line->storesRunning ?? null;
            $arrStoreRunningIds = $storesRunning->pluck('id')->toArray();
            $lineStatus         = $line->status;
            $weekdayName        = $this->getWeekdayName($line->day_of_week);
            $itemLine           = [
                'lineId'           => $line->id,
                'lineName'         => $lineName,
                'lineStatus'       => $lineStatus,
                'lineStore'        => $stores,
                'organizationName' => $organizationName,
                'organizationId'   => $organizationId,
                'weekdayName'      => implode(',<br/> ', $weekdayName),
                'findUserTDV'      => $line->findUserTDV,
                'tdv'              => [],
            ];

            // Lay so lan phai ghe tham toi thieu cua nha thuoc
            if (isset($stores) && $stores->count()) {
                $stores->map(function ($store) {
                    $store->numberVisit = $store->getNumberVisit();
                });
            }

            // Ghep store vao TDV
            $objTDVStore      = [];
            $userCheckinStore = [];
            $findUserTDV      = $line->findUserTDV;
            if (isset($findUserTDV) && $findUserTDV->count()) {
                foreach ($findUserTDV as $tdv) {
                    if (isset($requestParams['year']) && isset($requestParams['month'])) {
                        $checkinFrom                = \Carbon\Carbon::createFromDate($requestParams['year'], $requestParams['month'], 1)->toDateString();
                        $checkinTo                  = \Carbon\Carbon::createFromDate($requestParams['year'], $requestParams['month'])->endOfMonth()->toDateString();
                        $checkin                    = $tdv->checkin
                                ->where('forget', 0)
                                ->where('checkin_at', '>=', $checkinFrom)
                                ->where('checkin_at', '<=', $checkinTo)
                                ->pluck('store_id')->toArray() ?? null;
                        $userCheckinStore[$tdv->id] = array_count_values($checkin);
                    }
                    $tmpKey               = $tdv->id . '-' . $tdv->name;
                    $objTDVStore[$tmpKey] = $stores;
                }
            } else {
                $objTDVStore = [User::TDV_NULL => $stores];
            }
            $itemLine['tdv'] = $objTDVStore;

            // So lan ghe tham, ghe tham gan nhat, dat hang gan nhat (TDV + Store)
            $numRowSpanOfLine = isset($stores) && count($stores) ? count($itemLine['tdv']) * count($stores) : count($itemLine['tdv']);
            foreach ($itemLine['tdv'] as $keyTdv => $tdv) {
                if ($keyTdv == User::TDV_NULL) {
                    $tdv = $tdv->map(function ($item) {
                        $item->countVisit  = null;
                        $item->lastVisit   = null;
                        $item->lastBooking = null;
                        return $item;
                    });
                } else {
                    $explodeKey = explode('-', $keyTdv);
                    $tdvId      = $explodeKey[0] ?? '';
                    $tdv = $tdv->map(function ($item) use ($userCheckinStore, $tdvId, $line, $itemLine, $keyTdv) {
                        $item->countVisit  = $userCheckinStore[$tdvId][$item->id] ?? 0;
                        $item->lastVisit   = $this->checkinRepository->getLastCheckInOfTdvWithStore($tdvId, $item->id);
                        $item->lastBooking = $this->reportRevenueOrderRepository->getLastOrderByTDV($tdvId, $item->id);
                        return $item;
                    });
                }

                $itemLine['tdv'][$keyTdv] = $tdv;
            }

            $itemLine['numRowSpanOfLine']                     = $numRowSpanOfLine;
            $lineStoreUser[$line->organization_id]['rowspan'] += $numRowSpanOfLine;
            $lineStoreUser[$organizationId]['items'][]        = $itemLine;
        }

        $dataTable = [];
        foreach ($lineStoreUser as $localities) {
            $rowspanLocality = $localities['rowspan'];
            $localityIndex   = 0;

            foreach ($localities['items'] as $line) {
                $lineId           = $line['lineId'];
                $organizationName = $line['organizationName'];
                $lineName         = $line['lineName'];
                $weekdayName      = $line['weekdayName'];
                $lineStatus       = $line['lineStatus'];
                $rowspanLine      = $line['numRowSpanOfLine'] ?? null;
                $lineIndex        = 0;

                foreach ($line['tdv'] as $indexTDV => $stores) {
                    $nameIdTDV  = $indexTDV ? explode('-', $indexTDV) : null;
                    $nameTDV    = $nameIdTDV[1] . '-' . $nameIdTDV[0] ?? null;
                    $rowspanTDV = count($stores);
                    $tdvIndex   = 0;
                    $features   = '';
                    if ($lineStatus == Line::STATUS_ACTIVE) {
                        $features = '<a class="btn btn-sm btn-icon" href="' . route('admin.lines.edit', $lineId) . '">' .
                            '<i data-feather="edit" class="font-medium-2 text-body"></i></a>';
                    }
                    $bgClass = $lineStatus == Line::STATUS_INACTIVE ? 'bg-light' : null;

                    if ($rowspanTDV) {
                        foreach ($stores as $indexStore => $store) {
                            $storeName     = ($indexStore + 1) . '/ ' . $store['id'] . ' - ' . $store['code'] . ' - ' . $store['name'];
                            $numberVisit   = $store['numberVisit'] ?? null;
                            $latestCheckin = $store['lastVisit'] ? Carbon::parse($store['lastVisit'])->format('d-m-Y') : '';
                            $latestBooking = $store['lastBooking'] ? Carbon::parse($store['lastBooking'])->format('d-m-Y') : '';
                            $htmlCheckedIn = $store['countVisit'] >= $numberVisit ?
                                '<b class="text-success">' . $store['countVisit'] . '/' . $numberVisit . '</b>' :
                                '<b class="text-danger">' . $store['countVisit'] . '/' . $numberVisit . '</b>';

                            $row = [];
                            if ($localityIndex == 0) {
                                $row[] = ['value' => $organizationName, 'attr' => 'rowspan="' . $rowspanLocality . '"', 'class' => $bgClass];
                            }
                            if ($lineIndex == 0) {
                                $row[] = ['value' => $lineName, 'attr' => 'rowspan="' . $rowspanLine . '"', 'class' => $bgClass];
                                $row[] = ['value' => $weekdayName, 'attr' => 'rowspan="' . $rowspanLine . '"', 'class' => $bgClass];
                            }
                            if ($tdvIndex == 0) {
                                $row[] = ['value' => $nameTDV, 'attr' => 'rowspan="' . $rowspanTDV . '"', 'class' => $bgClass];
                            }
                            $row[] = ['value' => $storeName, 'attr' => null, 'class' => $bgClass];
                            $row[] = ['value' => $htmlCheckedIn, 'attr' => null, 'class' => 'text-center ' . $bgClass];
                            $row[] = ['value' => $latestCheckin, 'attr' => null, 'class' => 'text-center ' . $bgClass];
                            $row[] = ['value' => $latestBooking, 'attr' => null, 'class' => 'text-center ' . $bgClass];
                            if ($lineIndex == 0) {
                                $row[] = ['value' => $features, 'attr' => 'rowspan="' . $rowspanLine . '"', 'class' => 'text-center ' . $bgClass];
                            }

                            $localityIndex++;
                            $lineIndex++;
                            $tdvIndex++;
                            $dataTable[] = $row;
                        }
                    } else {
                        $rowspanTDV = 1;
                        $item       = [];
                        if ($localityIndex == 0) {
                            $item[] = ['value' => $organizationName, 'attr' => 'rowspan="' . $rowspanLocality . '"', 'class' => $bgClass];
                        }
                        if ($lineIndex == 0) {
                            $item[] = ['value' => $lineName, 'attr' => 'rowspan="' . $rowspanLine . '"', 'class' => $bgClass];
                            $item[] = ['value' => $weekdayName, 'attr' => 'rowspan="' . $rowspanLine . '"', 'class' => $bgClass];
                        }
                        $item[] = ['value' => $nameTDV, 'attr' => 'rowspan="' . $rowspanTDV . '"', 'class' => $bgClass];


                        $item[] = ['value' => null, 'attr' => null, 'class' => $bgClass];
                        $item[] = ['value' => null, 'attr' => null, 'class' => 'text-center ' . $bgClass];
                        $item[] = ['value' => null, 'attr' => null, 'class' => 'text-center ' . $bgClass];
                        $item[] = ['value' => null, 'attr' => null, 'class' => 'text-center ' . $bgClass];
                        if ($lineIndex == 0) {
                            $item[] = ['value' => $features, 'attr' => 'rowspan="' . $rowspanLine . '"', 'class' => 'text-center ' . $bgClass];
                        }

                        $localityIndex++;
                        $lineIndex++;
                        $dataTable[] = $item;
                    }
                }
            }
        }

        return [
            'dataTable' => $dataTable,
            'lines'     => $lines,
        ];
    }

    public function getDataForList($requestParams, $showOptions)
    {
        $requestParams  = $this->getRequestParams($requestParams);
        $monthSearch = Carbon::create($requestParams['year'] . '-' . $requestParams['month']);
        $requestParams['dateRange'] = [
            'from' => $monthSearch->startOfMonth()->format('Y-m-d'),
            'to' => $monthSearch->endOfMonth()->format('Y-m-d'),
        ];

        $lines = $this->repository->getForListScreen(
            $requestParams,
            $showOptions
        );

        $userIds = array_unique($lines->pluck('user_id')->toArray());

        $checkinInformation = $this->checkinRepository->getCheckinInfoOfIds(
            $userIds,
            $requestParams['dateRange']['from'],
            $requestParams['dateRange']['to']
        );

        $lastStoreOrders = $this->storeOrderRepository->getLastOrderOfUser(
            $userIds,
            $requestParams['dateRange']['from'],
            $requestParams['dateRange']['to']
        );

        return $lines->groupBy('organization_id')->map(function ($items) use ($checkinInformation, $lastStoreOrders) {
            return [
                'name' => $items->first()->organization_name,
                'items' => $items->groupBy('id')->map(function ($items) use ($checkinInformation, $lastStoreOrders) {
                    return [
                        'name' => $items->first()->name,
                        'day_of_week' => $items->first()->lineGroup->pluck('day_of_week')->toArray(),
                        'stores' => $items->first()->lineStores->pluck('id')->toArray(),
                        'items' => $items->groupBy('user_id')->map(function ($items) use ($checkinInformation, $lastStoreOrders) {
                            return [
                                'name' => $items->first()->user_name,
                                'items' => $items->map(function($item) use ($checkinInformation, $lastStoreOrders) {
                                    $checkinInfo = $checkinInformation
                                        ->where('user_id', $item->user_id)
                                        ->where('store_id', $item->store_id)
                                        ->first();

                                    $lastStoreOrder = $lastStoreOrders
                                        ->where('created_by', $item->user_id)
                                        ->where('store_id', $item->store_id)
                                        ->first();

                                    $item->last_checkin = isset($checkinInfo)
                                        ? Carbon::create($checkinInfo?->last_checkin)->format('d-m-Y')
                                        : '';
                                    $item->checkin_qty = $checkinInfo?->checkin_qty;
                                    $item->last_booking_at = isset($lastStoreOrder)
                                        ? Carbon::create($lastStoreOrder?->last_booking_at)->format('d-m-Y')
                                        : '';
                                    return $item;
                                })
                            ];
                        })
                    ];
                })
            ];
        });
    }

    public function deleteLine($id)
    {
        try {
            $line = $this->repository->find($id, ['lineStores', 'lineGroup']);

            if ($line) {
                if (count($line->lineStores)) {
                    return [
                        'result'  => false,
                        'message' => 'Tuyến đã có thông tin về nhà thuốc không được phép xóa',
                        'status'  => Response::HTTP_BAD_REQUEST
                    ];
                }

                $line->lineGroup()->delete();

                $this->repository->delete($id);

                return [
                    'result'  => true,
                    'message' => 'Xóa tuyến thành công',
                    'status'  => Response::HTTP_OK
                ];
            }

            return [
                'result'  => false,
                'message' => 'Tuyến không tồn tại',
                'status'  => Response::HTTP_NOT_FOUND
            ];
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ' error ' . $e->getMessage());
            Log::error($e);

            return [
                'result'  => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại sau.',
                'status'  => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }
    }
}
