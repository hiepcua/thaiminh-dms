<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Helpers\TableHelper;
use App\Models\Organization;
use App\Models\StoreOrder;
use App\Repositories\KeyRewardOrder\KeyRewardOrderRepositoryInterface;
use App\Repositories\Organization\OrganizationRepository;
use App\Repositories\User\UserRepository;
use App\Services\BaseService;
use Carbon\Carbon;

class KeyRewardOrderService extends BaseService
{
    protected $repository;

    public function __construct(
        KeyRewardOrderRepositoryInterface $repository,
        OrganizationRepository            $organizationRepository,
        UserRepository                    $userRepository,

    )
    {
        parent::__construct();

        $this->repository             = $repository;
        $this->organizationRepository = $organizationRepository;
        $this->userRepository         = $userRepository;
    }

    public function setModel()
    {
        return new StoreOrder();
    }

    public function indexOptions(): array
    {
        $current_user        = Helper::currentUser();
        $user_organization   = Helper::getUserOrganization();
        $search_division_id  = request('search.division_id');
        $show_division_field = $show_locality_field = $show_tdv_field = true;
        $count_division      = count($user_organization[Organization::TYPE_KHU_VUC] ?? []);
        $count_locality      = count($user_organization[Organization::TYPE_DIA_BAN] ?? []);
        if ($count_division == 1) {
            $show_division_field = false;
            $search_division_id  = array_shift($user_organization[Organization::TYPE_KHU_VUC]);
        }
        if ($count_locality == 1) {
            $show_locality_field = false;
        }
        if ($current_user?->roles?->first()->name == 'TDV') {
            $show_tdv_field = false;
        }
        $locality_id = request('search.locality_id');

        $searchCode    = request('search.code');
        $default_range = null;
//        if (!$searchCode) {
//            $default_range = request('search.range_date', (!request('search.code') ? implode(' to ', Helper::defaultMonthFromToDate()) : ''));
//        }
        $default_range = request('search.range_date', (!request('search.code') ? implode(' to ', Helper::defaultMonthFromToDate()) : ''));

        $searchOptions   = [];
        $searchOptions[] = [
            'type'         => 'text',
            'name'         => 'search[pharmacy]',
            'placeholder'  => 'Mã/Tên nhà thuốc',
            'defaultValue' => !$searchCode ? request('search.pharmacy') : '',
        ];
        $searchOptions[] = [
            'type'         => 'text',
            'name'         => 'search[order_code]',
            'placeholder'  => 'Mã đơn',
            'class'        => 'col-md-1',
            'defaultValue' => !$searchCode ? request('search.order_code') : '',
        ];
        if ($show_division_field) {
            $searchOptions[] = [
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
                        'id'         => 'division_id',
                        'attributes' => '',
                        'selected'   => !$searchCode ? request('search.division_id') : ''
                    ]
                ],
            ];
        }
        if ($show_locality_field) {
            $localityOptions = $search_division_id ? $this->organizationRepository->getLocalityByDivision($search_division_id)->pluck('name', 'id')->toArray() : [];
            $searchOptions[] = [
                'type'          => 'selection',
                'name'          => 'search[locality_id]',
                'defaultValue'  => $locality_id,
                'id'            => 'form-locality_id',
                'options'       => ['' => '- Địa bàn -'] + $localityOptions,
                'other_options' => ['option_class' => 'ajax-locality-option'],
            ];
        }

        $searchOptions[] = [
            'type'         => 'v2DateRangePicker',
            'placeholder'  => 'from - to',
            'name'         => 'search[range_date]',
            'defaultValue' => $default_range,
//                                        'id'           => 'searchRange',
        ];
        $delivery        = [
            '' => '- Đơn vị ship -',
            1  => 'Viettel ship'];
        if ($show_tdv_field) {
            if ($locality_id) {
                $delivery += $this->organizationRepository->getUserByLocality($locality_id)->pluck('name', 'id')->toArray();
            } else {
                $delivery += $this->userRepository->getByRole('TDV')->pluck('name', 'id')->toArray();
            }
            $searchOptions[] = [
                'type'          => 'select2',
                'name'          => 'search[order_logistic]',
                'defaultValue'  => request('search.order_logistic'),
                'id'            => 'form-created_by',
                'options'       => $delivery,
                'other_options' => ['option_class' => 'ajax-tdv-option'],
            ];
        }
        $searchOptions[] = [
            'type'         => 'selection',
            'name'         => 'search[status]',
            'defaultValue' => !$searchCode ? request('search.status') : '',
            'id'           => 'form-status',
            'options'      => [
                ''                           => '- Trạng thái đơn -',
                StoreOrder::STATUS_CHUA_GIAO => StoreOrder::STATUS_TEXTS[StoreOrder::STATUS_CHUA_GIAO],
                StoreOrder::STATUS_DA_GIAO   => StoreOrder::STATUS_TEXTS[StoreOrder::STATUS_DA_GIAO],
            ],
        ];
        $actions         = [];
        if (Helper::userCan('change_ship_status')) {
            $actions[] = ['text' => 'Giao hàng', 'action' => route('admin.key-reward-order.change-status')];
        }
        return compact('searchOptions', 'actions');
    }

    public function getDataTable($requestParams = [], $showOption = [])
    {
        if (empty($requestParams['range_date'])) {
            $date_default                = request('search.range_date', implode(' to ', Helper::defaultMonthFromToDate()));
            $requestParams['range_date'] = $this->handleDateRangeData($date_default);
        } else {
            $requestParams['range_date'] = $this->handleDateRangeData($requestParams['range_date']);
        }
        $showOption = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [
                [
                    "column" => "store_orders.id",
                    "type"   => "DESC"
                ]
            ]
        ], $showOption);
        $list_order = $this->repository->getByRequest(
            with: [],
            requestParams: $requestParams,
            showOption: $showOption
        );

        $list_order->map(function ($pharmacy_item, $key) use ($requestParams) {
            $pharmacy_item->checkbox      = sprintf('<input class="form-check-input row-check" type="checkbox" value="%s">', $pharmacy_item->id);
            $pharmacy_item->pharmacy_name = '<a href="' . route('admin.key-reward-order.detail', $pharmacy_item->id) . '">' . $pharmacy_item->pharmacy_name . '</a>';
            $pharmacy_item->products      = '';
            foreach ($pharmacy_item->list_product as $_product) {
                $pharmacy_item->products .= sprintf('<div class="d-flex text-nowrap gap-1"><b class="text-primary">%s</b><span class="ms-auto">%s %s</span></div>',
                    $_product['code'], $_product['qty'],
                    ($_product['price_format'] ? '* ' . $_product['price_format'] . ' đ' : '* 0'));
            }
            $order_logistic = ($pharmacy_item->order_logistic == 1 ?
                'Viettel ship' :
                ($this->userRepository->getUserById($pharmacy_item->order_logistic)->name ?? '-'));

            $pharmacy_item->logistic_text  = $order_logistic . '<br>' .
                match ($pharmacy_item->status) {
                    StoreOrder::STATUS_CHUA_GIAO => '<div class="badge badge-light-primary rounded-3" style="padding: 5px 10px">' . StoreOrder::STATUS_TEXTS[StoreOrder::STATUS_CHUA_GIAO] . '</div>',
                    StoreOrder::STATUS_DA_GIAO => '<div class="badge badge-light-success rounded-3" style="padding: 5px 10px">' . StoreOrder::STATUS_TEXTS[StoreOrder::STATUS_DA_GIAO] . '</div>',
                    default => '<div class="badge badge-light-danger rounded-3" style="padding: 5px 10px">Không có</div>'
                };
            $pharmacy_item->date_ship_text = $pharmacy_item->booking_at . '<br>' . $pharmacy_item->delivery_at;

            $pharmacy_item->sum_total_amount = Helper::formatPrice($pharmacy_item->total_amount) . 'đ';

            return $pharmacy_item;
        });
        $classCustom = [
            'total_sub_amount' => 'text-end',
            'total_discount'   => 'text-end',
            'sum_total_amount' => 'text-end',
        ];

        $nameTable = 'key-reward-order';
        return new TableHelper(
            collections: $list_order,
            nameTable: $nameTable,
            classCustom: $classCustom,
        );
    }

    public function change_status(array $ids)
    {
        $current_user                = Helper::currentUser();
        $update_attrs                = [
            'updated_by' => $current_user->id,
        ];
        $update_attrs['status']      = StoreOrder::STATUS_DA_GIAO;
        $update_attrs['delivery_at'] = now()->format('Y-m-d');

        $this->repository->update_order($ids, $update_attrs);
    }

    public function exportKeyReward($hash_id, $requestParams, $showOptions)
    {
        if (empty($requestParams['range_date'])) {
            $date_default                = request('search.range_date', implode(' to ', Helper::defaultMonthFromToDate()));
            $requestParams['range_date'] = $this->handleDateRangeData($date_default);
        } else {
            $requestParams['range_date'] = $this->handleDateRangeData($requestParams['range_date']);
        }

        $query          = $this->repository->getDataExport(
            with: [],
            requestParams: $requestParams,
            showOption: $showOptions
        );
        $file_name      = 'danh_sach_don_tt_key' . '_' . Carbon::now()->timestamp . "_.csv";
        $export_data    = request()->get('export_key_reward_order', cache()->get($hash_id)) ?: [];
        $export_options = [
            'hash_id'   => $hash_id,
            'file_name' => $file_name,
            'file_dir'  => 'list_key_reward_export_excel',
            'headers'   => [
                "STT",
                "NT",
                "Mã NT",
                "SP",
                "Total amount",
            ],
            'limit'     => 500,
        ];
        if (!$export_data) {
            $export_options['total'] = $query->getQuery()->getCountForPagination();
        }
        $exportService = new ExportService($export_data, $query, $export_options);

        $count       = 1;
        $export_data = $exportService->exportProgress(function ($pharmacy_item, $key) use (&$count) {

            $pharmacy_item->products = '';
            foreach ($pharmacy_item->list_product as $_product) {
                $pharmacy_item->products .= sprintf('<div class="d-flex text-nowrap gap-1"><b class="text-primary">%s</b><span class="ms-auto">%s %s</span></div>',
                    $_product['code'], $_product['qty'],
                    ($_product['price_format'] ? '* ' . $_product['price_format'] . ' đ' : '* 0'));
            }
            $result = [
                $count,
                $pharmacy_item->pharmacy_name,
                $pharmacy_item->pharmacy_code,
                $pharmacy_item->products,
                Helper::formatPrice($pharmacy_item->total_amount) . 'đ',
            ];
            $count++;

            return $result;
        });

        return response()->json($export_data);
    }

    public function getTableDetailData($id)
    {
        $list_order = $this->repository->getListByIdOrder($id);

        $list_order->map(function ($pharmacy_item, $key) {
            $pharmacy_item->tdv = $this->userRepository->getUserById($pharmacy_item->created_by)->name ?? '-';

            foreach ($pharmacy_item->list_product as $_product) {
                $pharmacy_item->booking_at = date('d/m/Y', strtotime($pharmacy_item->booking_at));
                $pharmacy_item->products   .= sprintf('<div class="d-flex text-nowrap gap-1"><b class="text-primary">%s</b><span class="ms-auto">%s %s</span></div>',
                    $_product['code'], $_product['qty'],
                    ($_product['price_format'] ? '* ' . $_product['price_format'] . ' đ' : '* 0'));
            }

            $pharmacy_item->sum_total_amount = Helper::formatPrice($pharmacy_item->total_amount) . 'đ';

            return $pharmacy_item;
        });
        $classCustom = [
            'sum_total_amount' => 'text-end',
        ];

        return new TableHelper(
            collections: $list_order,
            nameTable: 'detail-key-reward-order',
            classCustom: $classCustom,
            isPagination: false,
        );

    }
}
