<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Helpers\TableHelper;
use App\Models\Line;
use App\Models\Organization;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\LineStore\LineStoreRepositoryInterface;
use App\Repositories\Line\LineRepositoryInterface;
use App\Models\LineStore;
use Carbon\Carbon;

class LineStoreService extends BaseService
{
    protected LineStoreRepositoryInterface $repository;
    protected LineRepositoryInterface $lineRepository;
    protected OrganizationRepositoryInterface $organizationRepository;

    public function __construct(
        LineStoreRepositoryInterface    $repository,
        LineRepositoryInterface         $lineRepository,
        OrganizationRepositoryInterface $organizationRepository,
    )
    {
        parent::__construct();
        $this->repository             = $repository;
        $this->lineRepository         = $lineRepository;
        $this->organizationRepository = $organizationRepository;
    }

    public function setModel()
    {
        return new LineStore();
    }

    public function formOptions($model = null): array
    {
        $model->load(['line', 'store', 'line.organizations', 'userCreatedBy']);
        $options                  = parent::formOptions($model);
        $currentUser              = Helper::currentUser();
        $options['roleName']      = $currentUser?->roles[0]?->name;
        $options['status']        = LineStore::STATUS_TEXTS;
        $options['store']         = $model->store ?? null;
        $options['line']          = $model->line ?? null;
        $options['userCreatedBy'] = $model->userCreatedBy ?? null;

        return $options;
    }

    public function getTable($requestParams = [], $showOption = [])
    {
        $showOption    = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [
                "column" => "created_at",
                "type"   => "DESC"
            ]
        ], $showOption);
        $currentUser   = Helper::currentUser();
        $requestParams = $this->getRequestParams($requestParams);
        $canEdit       = $currentUser->canany(['duyet_nha_thuoc_thay_doi_tuyen']);
        $canView       = $currentUser->canany(['xem_nha_thuoc_thay_doi_tuyen']);
        $results       = $this->repository->getByRequest(['line', 'store', 'line.organizations', 'userCreatedBy'], $requestParams, $showOption);
        $cur_page      = $results->currentPage();
        $per_page      = $results->perPage();
        $results->getCollection()->transform(function ($item, $loopIndex) use ($canEdit, $canView, $cur_page, $per_page) {
            $item->stt               = ($loopIndex + 1) + ($cur_page - 1) * ($per_page);
            $item->store_name        = $item->store?->name . ' - (' . $item->store?->code . ')' ?? null;
            $item->line_name         = $item->line?->name ?? null;
            $item->locality          = $item->line?->organizations?->name ?? null;
            $item->created_at_format = Carbon::parse($item->created_at)->format('d-m-Y, H:i');
            $item->user_updated      = $item->userCreatedBy->name ?? null;
            if ($item->status == LineStore::STATUS_ACTIVE) {
                $item->status = '<span class="badge bg-success rounded-3" style="padding: 5px 10px">' . LineStore::STATUS_TEXTS[LineStore::STATUS_ACTIVE] . '</span>';
            } else if ($item->status == LineStore::STATUS_INACTIVE) {
                $item->status = '<span class="badge bg-secondary rounded-3" style="padding: 5px 10px">' . LineStore::STATUS_TEXTS[LineStore::STATUS_INACTIVE] . '</span>';
            } else if ($item->status == LineStore::STATUS_PENDING) {
                $item->status = '<span class="badge bg-warning rounded-3" style="padding: 5px 10px">' . LineStore::STATUS_TEXTS[LineStore::STATUS_PENDING] . '</span>';
            } else if ($item->status == LineStore::STATUS_NOT_APPROVE) {
                $item->status = '<span class="badge bg-secondary rounded-3" style="padding: 5px 10px">' . LineStore::STATUS_TEXTS[LineStore::STATUS_NOT_APPROVE] . '</span>';
            } else {
                $item->status = '<span class="badge bg-secondary rounded-3" style="padding: 5px 10px">' . LineStore::STATUS_TEXTS[LineStore::STATUS_INACTIVE] . '</span>';
            }
            $item->features = "";
            if ($canView) {
                $item->features .= '<a class="btn btn-sm btn-icon"
                   href="' . route('admin.line-store-change.show', $item->id) . '">
                    <i data-feather="file-text" class="font-medium-2 text-body"></i>
                </a>';
            }
            if ($canEdit) {
                $item->features .= '<a class="btn btn-sm btn-icon"
                   href="' . route('admin.line-store-change.edit', $item->id) . '">
                    <i data-feather="edit" class="font-medium-2 text-body"></i>
                </a>';
            }

            return $item;
        });
        $nameTable = Helper::userCan('xem_nha_thuoc_thay_doi_tuyen') || Helper::userCan('duyet_nha_thuoc_thay_doi_tuyen') ? 'line-store-list' : '';

        return new TableHelper(
            collections: $results,
            nameTable: $nameTable,
        );
    }

    public function indexOptions(): array
    {
        $user_organization   = Helper::getUserOrganization();
        $search_division_id  = request('search.division_id');
        $show_division_field = $show_locality_field = true;
        $count_division      = count($user_organization[Organization::TYPE_KHU_VUC] ?? []);
        $count_locality      = count($user_organization[Organization::TYPE_DIA_BAN] ?? []);
        if ($count_division == 1) {
            $show_division_field = false;
            $search_division_id  = array_shift($user_organization[Organization::TYPE_KHU_VUC]);
        }
        if ($count_locality == 1) {
            $show_locality_field = false;
        }

        $searchOptions   = [];
        $searchOptions[] = [
            'wrapClass'    => 'col-md-2',
            'type'         => 'text',
            'name'         => 'search[store-name]',
            'placeholder'  => 'Mã/Tên nhà thuốc',
            'defaultValue' => request('search.store-name'),
        ];

        $searchOptions[] = [
            'wrapClass'    => 'col-md-2',
            'type'         => 'text',
            'name'         => 'search[line-name]',
            'placeholder'  => 'Tên tuyến',
            'defaultValue' => request('search.line-name'),
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
                        'selected'   => request('search.division_id')
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

        $searchOptions[] = [
            'wrapClass'    => 'col-md-2',
            'type'         => 'selection',
            'name'         => 'search[status]',
            'defaultValue' => request('search.status'),
            'id'           => 'form-status',
            'options'      => [
                ''                            => '- Trạng thái -',
                LineStore::STATUS_ALL         => 'Tất cả',
                LineStore::STATUS_ACTIVE      => LineStore::STATUS_TEXTS[LineStore::STATUS_ACTIVE],
                LineStore::STATUS_INACTIVE    => LineStore::STATUS_TEXTS[LineStore::STATUS_INACTIVE],
                LineStore::STATUS_PENDING     => LineStore::STATUS_TEXTS[LineStore::STATUS_PENDING],
                LineStore::STATUS_NOT_APPROVE => LineStore::STATUS_TEXTS[LineStore::STATUS_NOT_APPROVE],
            ],
        ];

        return compact('searchOptions');
    }

    /**
     * @param $requestParams
     * @return mixed
     */
    public function getRequestParams($requestParams)
    {
        $currentUser                     = Helper::currentUser();
        $organizationOfCurrentUser       = Helper::getUserOrganization($currentUser);
        $userLocalities                  = $organizationOfCurrentUser[Organization::TYPE_DIA_BAN] ?? null; // Tat ca dia ban user duoc cho phep
        $roleName                        = $currentUser?->roles[0]?->name;
        $search_division                 = $requestParams['division_id'] ?? null;
        $search_locality                 = $requestParams['locality_ids'] ?? null;
        $requestParams['reference_type'] = LineStore::REFERENCE_TYPE_STORE;
        $requestParams['status']         = $requestParams['status'] ?? [LineStore::STATUS_PENDING];

        if (!$search_division && !$search_locality) {
            $requestParams['locality_ids'] = $userLocalities;
        } elseif (!$search_division && $search_locality) {
            $requestParams['locality_ids'] = array($search_locality);
        } elseif ($search_division && !$search_locality) {
            $requestParams['locality_ids'] = $this->organizationRepository->getLocalityByDivision($search_division)->pluck('id')->toArray();
        } elseif ($search_division && $search_locality) {
            $requestParams['locality_ids'] = array($search_locality);
        }

        return $requestParams;
    }

    public function create(array $attributes = [])
    {
        $currentUser           = Helper::currentUser();
        $arr                   = [];
        $arr['status']         = $attributes['status'] ?? null;
        $arr['reference_type'] = $attributes['reference_type'] ?? null;
        $arr['number_visit']   = $attributes['number_visit'] ?? LineStore::DEFAULT_NUMBER_VISIT;
        $from                  = $attributes['from'] ?? now()->toDateString();
        $lineStoreExist        = $this->repository->getLineStoreStatusExistInDay($attributes['line_id'], $attributes['store_id'], $from, $arr['reference_type'], $arr['number_visit']);

        if (isset($lineStoreExist) && count($lineStoreExist)) {
            foreach ($lineStoreExist as $lineStore) {
                $this->repository->reopenStoreInLine($lineStore->id, [
                    'to'           => null,
                    'number_visit' => $lineStore->number_visit ?? LineStore::DEFAULT_NUMBER_VISIT,
                    'updated_by'   => $currentUser->id,
                    'status'       => LineStore::STATUS_ACTIVE,
                ]);
            }
        } else {
            // Chua co ban ghi lineStore nao => Them moi
            $arr['line_id']  = $attributes['line_id'];
            $arr['store_id'] = $attributes['store_id'];
            $arr['from']     = $attributes['from'] ?? null;

            $this->repository->addNew($arr);
        }
    }

    // Dung khi them moi line
//    public function createWithMultipleStore($lineId = null, array $storeIds = [])
//    {
//        $currentUser = Helper::currentUser();
//        $line        = $this->lineRepository->find($lineId);
//        if ($line) {
//            foreach ($storeIds as $storeId){
//                $this->repository->processCreate([
//                    'line_id'        => $line->id ?? null,
//                    'store_id'       => $storeId ?? null,
//                    'from'           => $attributes['from'] ?? null,
//                    'to'             => null,
//                    'number_visit'   => $attributes['number_visit'] ?? null,
//                    'reference_type' => $attributes['reference_type'] ?? null,
//                    'created_by'     => $currentUser->id ?? null,
//                    'updated_by'     => null,
//                    'status'         => $attributes['status'] ?? null,
//                ]);
//            }
//            $line->stores()->syncWithPivotValues($storeIds, [
//                'from'           => now()->toDateString(),
//                'status'         => LineStore::STATUS_ACTIVE,
//                'created_at'     => now()->toDateTimeString(),
//                'updated_at'     => now()->toDateTimeString(),
//                'created_by'     => $currentUser->id ?? '',
//                'number_visit'   => LineStore::DEFAULT_NUMBER_VISIT,
//                'reference_type' => LineStore::REFERENCE_TYPE_LINE,
//            ]);
//        }
//    }

    public function closeStoresInLine(array $storeIds = [], $lineId = null)
    {
        $currentUser = Helper::currentUser();
        $lineStores  = $this->model
            ->where("line_id", $lineId)
            ->whereIn("store_id", $storeIds)
            ->get();

        foreach ($lineStores as $lineStore) {
            $lineStore->update([
                "to"         => now()->toDateString(),
                "status"     => LineStore::STATUS_INACTIVE,
                "updated_by" => $currentUser->id,
            ]);
        }
    }

    // Reactive nhung nha thuoc da bi inactive
//    public function reopenStoresInLine(array $arrStoreUpdateToNull = [], $lineId = null)
//    {
//        $currentUser = Helper::currentUser();
//        $lineStores  = $this->repository->getByStoreIds($arrStoreUpdateToNull, $lineId);
//        foreach ($lineStores as $lineStore) {
//            $lineStore->update([
//                'to'           => null,
//                'number_visit' => $attributes['number_visit'] ?? LineStore::DEFAULT_NUMBER_VISIT,
//                'updated_by'   => $currentUser->id,
//                'status'       => LineStore::STATUS_ACTIVE,
//            ]);
//        }
//    }

    // Lay nha thuoc dang hoat dong trong mot tuyen
    public function getLineStoreActive($lineId = null, $storeId = null)
    {
        $currentDate = now()->format('Y-m-d');
        return $this->model
            ->when($lineId ?? '', function ($query) use ($lineId) {
                return $query->where('line_id', $lineId);
            })
            ->when($storeId ?? '', function ($query) use ($storeId) {
                return $query->where('store_id', $storeId);
            })
            ->where(function ($query) use ($currentDate) {
                $query->where('from', '<=', $currentDate)
                    ->where('to', '>=', $currentDate)
                    ->orWhere(function ($q) use ($currentDate) {
                        $q->where('from', '<=', $currentDate)
                            ->where('to', null);
                    });
            })
            ->where('status', LineStore::STATUS_ACTIVE)
            ->get();
    }

    public function getByNewStore($newStoreId = null)
    {
        return $this->model
            ->where('store_id', $newStoreId)
            ->where('reference_type', LineStore::REFERENCE_TYPE_NEW_STORE)
            ->get();
    }

    /**
     * @param $storeId
     * Lay ra line_store ma nha thuoc dang hoat dong
     */
    public function getLineStoreRunningByStore($storeId = null)
    {
        return $this->model
            ->where('store_id', $storeId)
            ->whereNotNull('from')
            ->where('to', null)
            ->where('reference_type', '<>', LineStore::REFERENCE_TYPE_NEW_STORE)
            ->where('status', LineStore::STATUS_ACTIVE)
            ->get();
    }

    public function createLineStoreWhenApproveNewStore($lineId = null, $storeId = null, $number_visit = null, $lineStoreId = null)
    {
        $arr = [
            'line_id'        => $lineId,
            'store_id'       => $storeId,
            'from'           => now()->toDateString(),
            'to'             => null,
            'reference_type' => LineStore::REFERENCE_TYPE_STORE,
            'number_visit'   => $number_visit ?? LineStore::DEFAULT_NUMBER_VISIT,
            'status'         => LineStore::STATUS_ACTIVE,
            'created_by'     => Helper::currentUser()->id,
        ];

        $this->activeLineStoreOfNewStore($lineStoreId);
        $this->repository->create($arr);
    }

    public function activeLineStoreOfNewStore($lineStoreId = null)
    {
        $this->repository->update($lineStoreId, [
            'from'       => now()->toDateString(),
            'to'         => null,
            'status'     => LineStore::STATUS_ACTIVE,
            'updated_by' => Helper::currentUser()->id,
        ]);
    }

    public function closeWhenNotApprovedOrDeleteNewStore($lineStoreId)
    {
        $currentUser = Helper::currentUser();
        $arr         = [
            'status'     => LineStore::STATUS_NOT_APPROVE,
            'updated_by' => $currentUser->id,
        ];

        $this->repository->update($lineStoreId, $arr);
    }

    public function updateApproved(int $id, array $attributes)
    {
        $lineStore = $this->model->find($id);

        if ($attributes['status'] == LineStore::STATUS_ACTIVE) {
            $this->ASMApprove($lineStore->id);
        } else {
            $this->ASMNotApprove($lineStore->id);
        }
    }

    public function ASMApprove(int $id)
    {
        $lineStore = $this->model->find($id);
        if ($lineStore) {
            $lineStore->update([
                'from'       => now()->toDateString(),
                'status'     => LineStore::STATUS_ACTIVE,
                'updated_by' => Helper::currentUser()->id,
            ]);
        }
    }

    public function ASMNotApprove(int $id)
    {
        $lineStore = $this->model->find($id);
        if ($lineStore) {
            $lineStore->update([
                'status'     => LineStore::STATUS_NOT_APPROVE,
                'updated_by' => Helper::currentUser()->id,
            ]);
        }
    }

    // Admin, SA sua nha thuoc ma co cap nhat thay doi tuyen hoac so lan ghe tham
    public function storeEditLine($currentLineStoreId = null, array $attributes = [])
    {
        $currentUser   = Helper::currentUser();
        $currentDate   = now()->toDateString();
        $itemLineStore = [
            'line_id'        => $attributes['line_id'] ?? null,
            'store_id'       => $attributes['store_id'] ?? null,
            'number_visit'   => $attributes['number_visit'] ?? LineStore::DEFAULT_NUMBER_VISIT,
            'reference_type' => LineStore::REFERENCE_TYPE_STORE,
            'created_by'     => $currentUser->id,
            'status'         => LineStore::STATUS_ACTIVE,
        ];

        $lineStoreExist = $this->repository->getLineStoreStatusExistInDay(
            $itemLineStore['line_id'],
            $itemLineStore['store_id'],
            $currentDate,
            $itemLineStore['reference_type'],
            $itemLineStore['number_visit'])
            ->first();

        // Inactive store trong tuyen hien tai
        $lineStore = $this->repository->find($currentLineStoreId);
        if ($lineStore) {
            $lineStore->update([
                "to"         => now()->toDateString(),
                "status"     => LineStore::STATUS_INACTIVE,
                "updated_by" => $currentUser->id,
            ]);
        }

        if (!isset($lineStoreExist) || !$lineStoreExist->count()) {
            $this->repository->insertNewLineStore($itemLineStore); // Tao line_store moi
        } else {
            // Active line store da ton tai trong truong hop trong cung 1 ngay, 1 tuyen, 1 store, 1 type, 1 number visit
            $lineStoreExist->update([
                'to'         => null,
                'updated_by' => $currentUser->id,
                'status'     => LineStore::STATUS_ACTIVE,
            ]);
        }
    }

    // TDV tao line_store khi sua tuyen trong nha thuoc
    public function tdvEditLineInStore($storeId = null, $line_id = null, $number_visit = null)
    {
        $currentUser   = Helper::currentUser();
        $currentDate   = now()->toDateString();
        $itemLineStore = [
            'line_id'        => $line_id ?? null,
            'store_id'       => $storeId ?? null,
            'from'           => $currentDate,
            'to'             => null,
            'number_visit'   => $number_visit ?? LineStore::DEFAULT_NUMBER_VISIT,
            'reference_type' => LineStore::REFERENCE_TYPE_STORE,
            'created_by'     => $currentUser->id,
            'status'         => LineStore::STATUS_PENDING,
        ];

        $lineStoreExist = $this->repository->getLineStoreStatusExistInDay(
            $itemLineStore['line_id'],
            $itemLineStore['store_id'],
            $currentDate,
            $itemLineStore['reference_type'],
            $itemLineStore['number_visit'])
            ->first();

        // Ds line_store da ton tai voi line_id, store_id, reference_type, created_at
        $lineStores = $this->repository->getList([
            'line_id'        => $itemLineStore['line_id'],
            'store_id'       => $itemLineStore['store_id'],
            'created_at'     => $currentDate,
            'reference_type' => $itemLineStore['reference_type']
        ]);

        foreach ($lineStores as $_item) {
            $_item->update([
                "status"     => LineStore::STATUS_INACTIVE,
                "updated_by" => $currentUser->id,
            ]);
        }

        if (!isset($lineStoreExist) || !$lineStoreExist->count()) {
            $this->repository->insertNewLineStore($itemLineStore); // Tao line_store moi
        } else {
            // Active line store da ton tai trong truong hop trong cung 1 ngay, 1 tuyen, 1 store, 1 type, 1 number visit
            $lineStoreExist->update([
                'updated_by' => $currentUser->id,
                'status'     => LineStore::STATUS_PENDING,
            ]);
        }
    }
}
