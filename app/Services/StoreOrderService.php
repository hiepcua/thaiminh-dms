<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Helpers\TableHelper;
use App\Models\Mongodb\StoreOrderLog;
use App\Models\Organization;
use App\Models\ProductGroup;
use App\Models\Promotion;
use App\Models\Province;
use App\Models\Store;
use App\Models\StoreOrderItem;
use App\Repositories\Agency\AgencyRepositoryInterface;
use App\Repositories\Gift\GiftRepositoryInterface;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ProductGroupPriority\ProductGroupPriorityRepositoryInterface;
use App\Repositories\Promotion\PromotionRepositoryInterface;
use App\Repositories\ReportRevenueOrder\ReportRevenueOrderRepositoryInterface;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Repositories\StoreOrder\StoreOrderRepositoryInterface;
use App\Models\StoreOrder;
use App\Repositories\User\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;

class StoreOrderService extends BaseService
{
    public function __construct(
        protected StoreOrderRepositoryInterface           $repository,
        protected OrganizationRepositoryInterface         $organizationRepository,
        protected ProductRepositoryInterface              $productRepository,
        protected StoreRepositoryInterface                $storeRepository,
        protected PromotionRepositoryInterface            $promotionRepository,
        protected PromotionService                        $promotionService,
        protected GiftRepositoryInterface                 $giftRepository,
        protected ProductGroupPriorityRepositoryInterface $productGroupPriorityRepository,
        protected ReportRevenueOrderRepositoryInterface   $reportRevenueOrderRepository,
        protected AgencyRepositoryInterface               $agencyRepository,
        protected UserRepositoryInterface                 $userRepository,
        protected ReportRevenueStoreRankService           $reportRevenueStoreRankService,
        protected ReportAgencyInventoryService            $reportAgencyInventoryService
    )
    {
        parent::__construct();
    }

    public function setModel()
    {
        return new StoreOrder();
    }

    public function queryBookingDate()
    {
        return [
            'from' => now()->setDay(1)->format('Y-m-d'),
            'to'   => now()->format('Y-m-d')
        ];
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

        $searchCode      = request('search.code');
        $searchBookingAt = null;
        if (!$searchCode) {
            $searchBookingAt = request('search.booking_at', (!request('search.code') ? implode(' to ', $this->queryBookingDate()) : ''));
        }
        if ($searchCode) {
            $search_division_id = 0;
            $locality_id        = 0;
        }

        $searchOptions   = [];
        $searchOptions[] = [
            'type'         => 'dateRangePicker',
            "placeholder"  => "Ngày nhập from - to",
            "name"         => "search[booking_at]",
            "defaultValue" => $searchBookingAt,
            "id"           => "searchRange",
        ];
        $searchOptions[] = [
            'type'         => 'text',
            'name'         => 'search[store_code]',
            'placeholder'  => 'Mã nhà thuốc',
            'defaultValue' => !$searchCode ? request('search.store_code') : '',
        ];
        $searchOptions[] = [
            'type'         => 'text',
            'name'         => 'search[store_name]',
            'placeholder'  => 'Tên nhà thuốc',
            'defaultValue' => !$searchCode ? (!request('search.store_code') ? request('search.store_name') : '') : '',
        ];
        $searchOptions[] = [
            'type'         => 'text',
            'name'         => 'search[code]',
            'placeholder'  => 'Mã đơn hàng',
            'defaultValue' => $searchCode,
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
                        'class'      => '',
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
        $optionTDV = ['' => '- TDV -'];
        if ($show_tdv_field) {
            if ($locality_id) {
                $optionTDV += $this->organizationRepository->getUserByLocality($locality_id)->pluck('name', 'id')->toArray();
            } else {
                $optionTDV += $this->userRepository->getByRole('TDV')->pluck('name', 'id')->toArray();
            }
            $searchOptions[] = [
                'type'          => 'select2',
                'name'          => 'search[created_by]',
                'defaultValue'  => request('search.created_by'),
                'id'            => 'form-created_by',
                'options'       => $optionTDV,
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
        if (Helper::userCan('doi_trang_thai_giao_hang')) {
            $actions[] = ['text' => 'Giao hàng', 'action' => route('admin.store-orders.action', ['shipping'])];
        }
        if (Helper::userCan('doi_trang_thai_xoa_don')) {
            $actions[] = ['text' => 'Xóa đơn', 'action' => route('admin.store-orders.action', ['delete'])];
        }

        return compact('searchOptions', 'actions');
    }

    public function reportAgencyOrderOption(): array
    {
        $localityId         = request('search.locality_id', null);
        $agencyId           = request('search.agency_id', null);
        $searchBookingAt    = request('search.booking_at', (!request('search.code') ? implode(' to ', $this->queryBookingDate()) : ''));
        $search_division_id = request('search.division_id', null);
        $localityOptions    = $search_division_id
            ? $this->organizationRepository->getLocalityByDivision($search_division_id)->pluck('name', 'id')->toArray()
            : [];
        $agencyOptions      = $localityId
            ? $this->agencyRepository->getByLocality($localityId)
            : [];

        $searchOptions   = [];
        $searchOptions[] = [
            'type'         => 'dateRangePicker',
            "placeholder"  => "Ngày nhập from - to",
            "name"         => "search[booking_at]",
            "defaultValue" => $searchBookingAt,
            "id"           => "searchRange",
        ];
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
                    'class'      => '',
                    'id'         => 'division_id',
                    'attributes' => '',
                    'selected'   => request('search.division_id')
                ]
            ],
        ];
        $searchOptions[] = [
            'type'          => 'selection',
            'name'          => 'search[locality_id]',
            'defaultValue'  => $localityId,
            'id'            => 'form-locality_id',
            'options'       => ['' => '- Địa bàn -'] + $localityOptions,
            'other_options' => ['option_class' => 'ajax-locality-option'],
        ];
        $searchOptions[] = [
            'type'          => 'selection',
            'name'          => 'search[agency_id]',
            'defaultValue'  => $agencyId,
            'id'            => 'form-agency_id',
            'options'       => ['' => '- Đại lý -'] + $agencyOptions,
            'other_options' => ['option_class' => 'ajax-agency-option'],
        ];

        return compact('searchOptions');
    }

    public function getTable($requestParams = [], $showOption = [])
    {
        $results = $this->dataLists($requestParams, $showOption);
        $results->getCollection()->transform(function ($item) {
            $item->store_code       = $item->store->code ?? '';
            $item->store_name       = view('pages.store_orders.row-item-store-name', compact('item'))->render();
            $item->agency_name      = $item->agency?->name;
            $item->col_status       = sprintf('<span class="badge bg-%s">%s</span>', ($item->status == 2 ? 'success' : 'warning'), $item->status_name);
            $discount = Helper::formatPrice($item->discount, '');
            $discountPercent = round((($item->discount*100)/$item->sub_total), 2);
            $item->col_discount     = $discountPercent
                ? "<div class='w-100 d-flex align-items-center'><div style='font-size: 11px'>$discountPercent%</div><div class='ms-auto'>$discount</div></div>"
                : "<div class='w-100 text-end'>$discount</div>";
            $item->col_discount_mobile     = $discountPercent
                ? "<div class='w-100 d-flex align-items-center'><div class='ms-auto'>$discount ($discountPercent%)</div></div>"
                : "<div class='w-100 text-end'>$discount</div>";
            $item->col_total_amount = Helper::formatPrice($item->total_amount, '');
            $item->col_product      = '';
            foreach ($item->list_product as $_product) {
                $item->col_product .= sprintf('<div class="d-flex text-nowrap gap-1"><b class="text-primary">%s</b><span class="ms-auto">%s %s</span></div>',
                    $_product['code'], $_product['qty'],
                    ($_product['price_format'] ? '* ' . $_product['price_format'] : '* 0'));
            }
            $item->checkbox = sprintf('<input class="form-check-input row-check" type="checkbox" value="%s">', $item->id);

            $item->store_tdv = view('pages.store_orders.row-item-mobile', compact('item'))->render();

            return $item;
        });
        $nameTable = Helper::userCan('tdv_xem_don_hang_nt') ? 'store-order-list-tdv' : 'store-order-list';

        return new TableHelper(
            collections: $results,
            nameTable: $nameTable,
        );
    }

    protected function handleRequestReportStatementAgency($requestParams = [])
    {
        if (empty($requestParams['booking_at'])) {
            $requestParams['booking_at'] = $this->queryBookingDate();
        } else {
            $requestParams['booking_at'] = $this->handleDateRangeData($requestParams['booking_at']);
        }

        if (!isset($requestParams['agency_id'])) {
            if (isset($requestParams['locality_id'])) {
                $requestParams['organization_ids'] = [$requestParams['locality_id']];
            } else if (isset($requestParams['division_id'])) {
                $requestParams['organization_ids'] = $this->organizationRepository
                    ->getLocalityByDivision($requestParams['division_id'])->pluck('id')->toArray();
            }
        } else {
            $requestParams['organization_ids'] = [];
        }

        return $requestParams;
    }

    public function getTableStatementAgency($requestParams = [], $showOption = [])
    {
        $showOption = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [[
                "column" => "store_orders.created_at",
                "type"   => "DESC"
            ]]
        ], $showOption);

        $requestParams = $this->handleRequestReportStatementAgency($requestParams);

        $products       = [];
        $productHeaders = [];
        $customClass    = [];
        $footerRow      = [
            "locality"     => ['attribute' => 'colspan="7"', 'value' => 'Tổng cộng'],
            "sub_total"    => 0,
            "discount"     => 0,
            "total_amount" => 0,
            "items"        => []
        ];

        $results = $this->repository->getDataForStatementAgency($requestParams, $showOption);
        foreach ($results as $result) {
            foreach ($result->items as $item) {
                if ($item->product_type == StoreOrderItem::PRODUCT_TYPE_PRODUCT) {
                    $products[$result->id][$item->product_name] = $item->product_qty;
                    $footerRow['items'][$item->product_name]    = 0;
                    $productHeaders[$item->product_name]        = $item->product_name;
                    $customClass[$item->product_name]           = 'text-end text-nowrap';
                }
            }
        }

        $results->getCollection()->transform(function ($item) use ($productHeaders, $products, &$footerRow) {
            $item->agency_name         = $item->agency?->name;
            $item->locality            = $item->organization?->name;
            $item->store_code          = $item->store?->code;
            $item->store_name          = $item->store?->name;
            $item->delivery_at         = Carbon::create($item->delivery_at)->format('d/m/Y');
            $item->address             = $item->store?->full_address;
            $item->phone               = Helper::correctPhone($item->store?->phone_owner);
            $item->tdv_name            = $item->sale->name;
            $footerRow['sub_total']    += $item->sub_total;
            $footerRow['discount']     += $item->discount;
            $footerRow['total_amount'] += $item->total_amount;
            $item->sub_total           = Helper::formatPrice($item->sub_total);
            $item->discount            = Helper::formatPrice($item->discount);
            $item->total_amount        = Helper::formatPrice($item->total_amount);

            foreach ($productHeaders as $key => $productHeader) {
                $item->$key                         = $products[$item->id][$key] ?? null;
                $footerRow['items'][$productHeader] += ($products[$item->id][$key] ?? 0);
            }

            return $item;
        });
        $nameTable = 'statement_for_agency';

        $footerRow               = (object)$footerRow;
        $footerRow->sub_total    = Helper::formatPrice($footerRow->sub_total);
        $footerRow->discount     = Helper::formatPrice($footerRow->discount);
        $footerRow->total_amount = Helper::formatPrice($footerRow->total_amount);

        $footerRowHtml = "<tr class='bg-secondary text-white'>
                        <td class='text-center' colspan='7'>Tổng cộng</td>
                        <td class='text-end'>$footerRow->sub_total</td>
                        <td class='text-end'>$footerRow->discount</td>
                        <td class='text-end'>$footerRow->total_amount</td>
                        <td class='text-center'>-</td>
                        <td class='text-center'>-</td>";

        foreach ($footerRow->items as $footerItem) {
            $footerRowHtml .= "<td class='text-end'>$footerItem</td>";
        }
        $footerRowHtml .= '</tr>';

        return new TableHelper(
            collections: $results,
            nameTable: $nameTable,
            headers: config("table.pages.$nameTable.headers", []) + $productHeaders,
            classCustom: config("table.pages.$nameTable.classCustom", []) + $customClass,
            footerRow: $footerRowHtml
        );
    }

    public function exportReport($hash_id, $requestParams, $showOption)
    {
        $requestParams = $this->handleRequestReportStatementAgency($requestParams);

        $query = $this->repository->getQueryForStatementAgency($requestParams);

        foreach ($showOption['orderBy'] ?? [] as $orderBy) {
            if (isset($orderBy['column'])) {
                $query->orderBy($orderBy['column'], $orderBy['type'] ?? 'DESC');
            }
        }
        $products = $this->repository->getProductInOrderList($requestParams);

        $orderCode = (clone $query)->first()->order_code;

        $file_name   = $orderCode . '_' . Carbon::now()->format('Y_m_d') . "_.csv";
        $export_data = request()->get('export_statement_agency', cache()->get($hash_id)) ?: [];

        $export_options = [
            'hash_id'   => $hash_id,
            'file_name' => $file_name,
            'file_dir'  => 'agency_order',
            'headers'   => [
                'STT',
                'Địa bàn',
                'Mã NT',
                'Tên NT',
                'Địa chỉ',
                'SĐT',
                'TDV',
                'Ngày giao',
                'Doanh số',
                'Chiết khấu',
                'Phải thu',
                'Đại lý',
                'Ghi chú',
            ],
            'limit'     => 500,
        ];

        foreach ($products as $productName) {
            $export_options['headers'][$productName] = $productName;
        }

        if (!$export_data) {
            $export_options['total'] = $query->getQuery()->getCountForPagination();
        }

        $exportService = new ExportService($export_data, $query, $export_options);

        $countOrder  = 1;
        $export_data = $exportService->exportProgress(handelResult: function ($results) use (&$countOrder, $products) {
            $totalSubAmount = $totalDiscount = $totalFinalAmount = 0;
            $productQty     = [];
            $results        = $results->map(function ($order, $index)
            use (&$countOrder, $products, &$totalSubAmount, &$totalDiscount, &$totalFinalAmount, &$productQty) {
                $totalSubAmount   += $order->sub_total;
                $totalDiscount    += $order->discount;
                $totalFinalAmount += $order->total_amount;

                $result = [
                    $countOrder,
                    $order->organization?->name,
                    $order->store?->code,
                    $order->store?->name,
                    $order->store?->full_address,
                    Helper::correctPhone($order->store?->phone_owner),
                    $order->sale?->name,
                    $order->booking_at,
                    $order->sub_total,
                    $order->discount,
                    $order->total_amount,
                    $order->agency?->name,
                    $order->note
                ];

                foreach ($products as $key => $product) {
                    $qty = $order->items->where('product_name', $product)->first()?->product_qty;
                    $result[] = $qty;
                    if (isset($productQty[$key])) {
                        $productQty[$key] += $qty;
                    } else {
                        $productQty[$key] = $qty;
                    }
                }

                $countOrder++;

                return $result;
            })
                ->toArray();

            if (count($results) > 1) {
                $totalRow = [
                    'Tổng',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $totalSubAmount,
                    $totalDiscount,
                    $totalFinalAmount,
                    '',
                    ''
                ];

                foreach ($productQty as $qty) {
                    $totalRow[] = $qty;
                }

                $results[] = $totalRow;
            }

            return $results;
        });

        return response()->json($export_data);
    }

    public function dataLists($search, $showOption = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $locality_id = $search['locality_id'] ?? '';
        if (Helper::userCan('loc_du_lieu_cay_so_do')) {
            $user_organization = Helper::getUserOrganization();
            $user_locality_ids = ($user_organization[Organization::TYPE_DIA_BAN] ?? [-1]);
            if (!$locality_id) {
                $search['locality_ids'] = $user_locality_ids;
            } elseif (!in_array($locality_id, $user_locality_ids)) {
                $search['locality_ids'] = $user_locality_ids;
            } else {
                $search['locality_ids'] = [$locality_id];
            }
            if (Helper::isTDV()) {
                $search['created_by'] = Helper::currentUser()->id;
            }
        } else {
            $division_id = $search['division_id'] ?? '';
            if (!$locality_id && $division_id) {
                $search['locality_ids'] = $this->organizationRepository->getLocalityByDivision($division_id)->pluck('id')->toArray();
            } elseif ($locality_id) {
                $search['locality_ids'] = [$locality_id];
            }
        }
        $search['status'] = $search['status'] ?? [StoreOrder::STATUS_CHUA_GIAO, StoreOrder::STATUS_DA_GIAO];
        if (empty($search['booking_at'])) {
            $search['booking_at'] = $this->queryBookingDate();
        } else {
            $search['booking_at'] = $this->handleDateRangeData($search['booking_at']);
        }
        foreach (['store_code', 'code'] as $key) {
            if (!empty($search[$key])) {
                $tmp          = explode(',', $search[$key]);
                $tmp          = array_map('trim', $tmp);
                $search[$key] = array_filter($tmp);
            }
        }

        if (!empty($search['code'])) {
            $search = ['code' => $search['code']];
        }
//        $search['parent_id'] = 0;
//        $search['order_type'] = StoreOrder::ORDER_TYPE_DON_THUONG;

        return $this->repository->getByRequest($showOption['perPage'] ?? 20,
            [
                'store',
                'store.ward',
                'store.district',
                'store.province',
                'items.product',
                'agency', 'sale', 'organization.parent.users'
            ], $search);
    }

    public function order_action(string $type, array $ids)
    {
        $current_user = Helper::currentUser();
        $update_attrs = [
            'updated_by' => $current_user->id,
        ];
        if ($type == 'shipping') {
            $update_attrs['status']      = StoreOrder::STATUS_DA_GIAO;
            $update_attrs['delivery_at'] = now()->format('Y-m-d');
        } elseif ($type == 'delete') {
            $update_attrs['status'] = StoreOrder::STATUS_DA_XOA;
        }

        StoreOrder::query()
            ->whereIn('id', $ids)
            ->where('status', StoreOrder::STATUS_CHUA_GIAO)
            ->update($update_attrs);
    }

    public function generateCode($prefix = ''): string
    {
        $code = $prefix . now()->format('ym');//4
        $code .= mt_rand(100000, 999999);

        if (!$this->repository->checkCodeExists($code)) {
            return $code;
        }

        return $this->generateCode();
    }

    public function create(array $attributes)
    {
        $current_user       = Helper::currentUser();
        $current_date       = now()->format('Y-m-d');
        $product_priorities = $this->productGroupPriorityRepository->getList([], ['minDate' => $current_date, 'maxDate' => $current_date]);
        $product_priorities = collect($product_priorities);
        $store              = $this->storeRepository->findOrFail($attributes['store_id']);
        if (empty($attributes['organization_id']) && $store) {
            $organization = $this->organizationRepository->find($store->organization_id);
        } else {
            $organization = $this->organizationRepository->find($attributes['organization_id']);
        }
        if (empty($attributes['booking_at'])) {
            $attributes['booking_at'] = $current_date;
        }

        $order_attributes = [
            'user_id'           => $current_user->id,
            'organization_id'   => $attributes['organization_id'] ?? $store->organization_id,
            'agency_id'         => $attributes['agency_id'] ?? null,
            'code'              => $this->generateCode(),
            'booking_at'        => $attributes['booking_at'],
            'sub_total'         => 0,
            'discount'          => 0,
            'total_amount'      => 0,
            'store_id'          => $attributes['store_id'],
            'store_province_id' => $store->province_id,
            'store_district_id' => $store->district_id,
            'store_ward_id'     => $store->ward_id,
            'paid'              => 0,
            'note'              => $attributes['note'],
            'created_by'        => $current_user->id,
            'agency_status'     => StoreOrder::AGENCY_STATUS_CHUA_THANH_TOAN,
            'order_type'        => $attributes['order_type'] ?? StoreOrder::ORDER_TYPE_DON_THUONG,
            'order_logistic'    => $attributes['order_logistic'] ?? 0,
            'parent_id'         => 0,
            'ttk_product_type'  => $attributes['product_type'] ?? null,
        ];
        if ($order_attributes['order_logistic']) {
            $order_attributes['order_logistic_type'] = $order_attributes['order_logistic'] == StoreOrder::ORDER_LOGISTIC_VIETTEL ?
                StoreOrder::ORDER_LOGISTIC_TYPE_VIETTEL : StoreOrder::ORDER_LOGISTIC_TYPE_TDV;
        }
        if ($order_attributes['order_type'] == StoreOrder::ORDER_TYPE_DON_TTKEY) {
            $order_attributes['parent_id'] = -1;
            $order_attributes['agency_id'] = null;
        }
        if (in_array($organization->province_id, [Province::HN_ID, Province::HCM_ID])) {
            $order_attributes['status'] = StoreOrder::STATUS_CHUA_GIAO;
        } else {
            $order_attributes['status']      = StoreOrder::STATUS_DA_GIAO;
            $order_attributes['delivery_at'] = now()->format('Y-m-d');
        }

        $discountDetails = [];
        collect($attributes['products'])->where('type', StoreOrderItem::PRODUCT_TYPE_DISCOUNT)->each(function ($item) use (&$discountDetails) {
            if ($item['details']) {
                $details = json_decode($item['details'], true) ?? [];
                foreach ($details as $pid => $value) {
                    foreach ($value['details'] as $detailItem) {
                        $discountDetails[$pid][] = array_merge([
                            'promo_id' => $item['promo_id'], 'promo_name' => $item['promo_name'], 'condition_name' => $item['condition_name']
                        ], $detailItem);
                    }
                }
            }
        });

        $item_attributes = [];
        foreach ($attributes['products'] as $_product) {
            if ($_product['type'] == StoreOrderItem::PRODUCT_TYPE_DISCOUNT) {
                continue;
            }

            $product_priority = $_product['type'] == StoreOrderItem::PRODUCT_TYPE_PRODUCT ? $product_priorities->where('product_id', $_product['id'])->first() : null;
            $amount           = $_product['price'] * $_product['qty'];
            if ($_product['type'] == StoreOrderItem::PRODUCT_TYPE_GIFT) {
                $amount = 0;
            }
            $_productId     = $_product['id'] ?? 0;
            $item_attribute = [
                'product_type'         => $_product['type'],
                'product_id'           => $_productId,
                'product_group_id'     => $product_priority['group_id'] ?? 0,
                'product_sub_group_id' => $product_priority['sub_group_id'] ?? 0,
                'product_priority'     => $product_priority['priority'] ?? 0,
                'product_name'         => $_product['name'],
                'product_price'        => $_product['price'],
                'product_qty'          => $_product['qty'],
                'promo_id'             => $_product['promo_id'] ?? 0,
                'discount'             => 0,
                'discount_detail'      => [],
                'sub_total'            => $amount,
                'total_amount'         => $amount,
                'booking_at'           => $attributes['booking_at'],
            ];
            if ($_product['type'] == StoreOrderItem::PRODUCT_TYPE_PRODUCT) {
                $order_attributes['sub_total']    += $amount;
                $order_attributes['total_amount'] += $amount;
                if (isset($discountDetails[$_productId])) {
                    $item_attribute['discount']        = collect($discountDetails[$_productId])->sum('discount');
                    $item_attribute['total_amount']    -= $item_attribute['discount'];
                    $item_attribute['discount_detail'] = $discountDetails[$_productId];

                    $order_attributes['total_amount'] -= $item_attribute['discount'];
                    $order_attributes['discount']     += $item_attribute['discount'];
                }
            } else {
                $order_attributes['total_amount'] -= $amount;
                $order_attributes['discount']     += $amount;
            }

            $item_attributes[] = $item_attribute;
        }
        $store_order = $this->repository->create($order_attributes);
        foreach ($item_attributes as $item_attribute) {
            $item_attribute['store_order_id'] = $store_order->id;
            $orderItem = new StoreOrderItem();
            $orderItem->fill($item_attribute);
            $orderItem->save();
            if ($orderItem->product_type == StoreOrderItem::PRODUCT_TYPE_PRODUCT) {
                $bookingAt = Carbon::create($orderItem->booking_at);
                $importInventoryQty = $orderItem->product_qty ? 0 : abs($orderItem->product_qty);
                $exportInventoryQty = $orderItem->product_qty ?? 0;

                $this->reportAgencyInventoryService->updateInventory(
                    $store_order->agency_id,
                    $orderItem->product_id,
                    $bookingAt->year,
                    $bookingAt->month,
                    $exportInventoryQty,
                    $importInventoryQty
                );
            }
        }
        $storeOrderLog                 = new StoreOrderLog();
        $storeOrderLog->store_order_id = $store_order->id;
        $storeOrderLog->type           = 'create';
        foreach ($attributes as $_key => $_value) {
            if ($_key == '_token') {
                continue;
            }
            $storeOrderLog->{$_key} = $_value;
        }
        $storeOrderLog->save();

        return $store_order;
    }

    public function createParentTTKey($childOrder)
    {
        $results = $this->repository->getOrderTTKey($childOrder->store_id, $childOrder->ttk_product_type, StoreOrder::STATUS_CHUA_GIAO);
        if ($results['orders']->isEmpty()) {
            $parentOrder = new StoreOrder();
            $parentOrder->fill($childOrder->toArray());
            $parentOrder->parent_id = 0;
            $parentOrder->status    = StoreOrder::STATUS_CHUA_GIAO;
            $parentOrder->code      = $this->generateCode('K');
            $countChild             = 1;
        } else {
            $parentOrder               = $results['orders']->first();
            $parentOrder->discount     += $childOrder->discount;
            $parentOrder->sub_total    += $childOrder->sub_total;
            $parentOrder->total_amount += $childOrder->total_amount;
            $countChild                = $this->repository->countOrderChild($parentOrder->id);
        }
        $parentOrder->code .= '_' . $countChild;
        $parentOrder->save();
        $parentId              = $parentOrder->id;
        $childOrder->parent_id = $parentId;
        $childOrder->save();

        $childOrder->items()->each(function ($item) use ($parentId) {
            $orderItem = new StoreOrderItem();
            $orderItem->fill($item->toArray());
            $orderItem->store_order_id = $parentId;
            $orderItem->save();
        });
    }

    public function formOptions($model = null): array
    {
        $currentUser      = Helper::currentUser();
        $options          = parent::formOptions($model);
        $userOrganization = Helper::getUserOrganization();
        if (!$options['default_values']['organization_id'] && $userOrganization) {
            if (count($userOrganization[Organization::TYPE_DIA_BAN]) == 1) {
                $options['default_values']['organization_id'] = array_key_first($userOrganization[Organization::TYPE_DIA_BAN]);
            }
        }
        $options['organization'] = Helper::getTreeOrganization(
            currentUser: true,
            activeTypes: [
                Organization::TYPE_DIA_BAN,
            ],
            excludeTypes: [
                Organization::TYPE_TONG_CONG_TY,
                Organization::TYPE_CONG_TY,
                Organization::TYPE_MIEN,
                Organization::TYPE_KHU_VUC,
            ],
            setup: [
                'multiple'   => false,
                'name'       => 'organization_id',
                'class'      => 'control-order-setup-area',
                'id'         => 'form-organization_id',
                'attributes' => 'required aria-describedby="form-organization_id-error"',
                'selected'   => $options['default_values']['organization_id']
            ],
            defaultOptionText: "Địa bàn"
        );
        if (!$model) {
            $options['default_values']['booking_at'] = now()->format('Y-m-d');
        }
        $options['default_values']['promotions']   = old('promotions') ?: [];
        $options['default_values']['products']     = old('products') ?: [];
        $options['default_values']['product_type'] = old('product_type') ?: '';

        // DATA BY LOCALITY
        if ($options['default_values']['organization_id']) {
            $data_by_locality = $this->getDataByLocality($options['default_values']['organization_id'], $options['default_values']);
        } else {
            $data_by_locality = $this->getDataByLocality([], $options['default_values']);
        }
        $options['products']        = $data_by_locality['products'];
        $options['product_types']   = $data_by_locality['product_types'];
        $options['agencies']        = $data_by_locality['agencies'];
        $options['stores']          = $data_by_locality['stores'];
        $options['promotions']      = $data_by_locality['promotions'];
        $options['promotion_view']  = $data_by_locality['promotion_view'];
        $options['order_types']     = $data_by_locality['order_types'];
        $options['order_logistics'] = $data_by_locality['order_logistics'];
        $options['product_options'] = $data_by_locality['product_options'];
        $options['show_product']    = $data_by_locality['show_product'];

        if (empty($options['default_values']['order_logistic'])) {
            $options['default_values']['order_logistic'] = $currentUser->id;
        }
        if ($options['default_values']['products']) {
            foreach ($options['default_values']['products'] as $_id => &$p_values) {
                if ($p_values['type'] != 'gift') {
                    $amount                    = $p_values['qty'] * $p_values['price'];
                    $p_values['amount']        = $amount;
                    $p_values['amount_format'] = Helper::formatPrice($amount, '');
                } else {
                    $p_values['amount']        = 0;
                    $p_values['amount_format'] = '';
                }
                $p_values['price_format'] = Helper::formatPrice($p_values['price'], '') ?: '';
                $p_values['promo_id']     = (int)$p_values['promo_id'];
            }
        }
        unset($p_values);

        return $options;
    }

    function _parseOptions($agencyItems, $storeItems): array
    {
        $agencies = $agencyItems->map(function ($agency) {
            $agency->display_name = $agency->code . ' - ' . $agency->name;
            return $agency;
        })->pluck('display_name', 'id');
        $stores   = $storeItems->map(function ($store) {
            $store->display_name = $store->code . ' - ' . $store->name;
            return $store;
        })->pluck('display_name', 'id');

        return compact('agencies', 'stores');
    }

    public function getDataByLocality($localityId, $defaultValues = []): array
    {
        $currentUser         = Helper::currentUser();
        $userHasOrganization = $currentUser->organizations->isNotEmpty();
        if (!$localityId && $userHasOrganization) {
            $localityId = $currentUser->organizations->pluck('id')->toArray();
        }

        $divisionId = [];
        if (!$localityId && $userOrganizations = Helper::getUserOrganization()) {
            if (count($userOrganizations[Organization::TYPE_DIA_BAN]) > 1) {
                $localityId = $userOrganizations[Organization::TYPE_DIA_BAN];
            } else {
                $localityId = array_key_first($userOrganizations[Organization::TYPE_DIA_BAN]);
            }
            $divisionId = $userOrganizations[Organization::TYPE_KHU_VUC];
        }
        if (is_array($localityId)) {
            $organizations = $this->organizationRepository->getByArrId($localityId, ['agency', 'stores']);
            $agencies      = collect([]);
            $stores        = collect([]);
            foreach ($organizations as $organization) {
                $agencies = $agencies->merge($organization->agency)->unique('id');
                $stores   = $stores->merge($organization->stores)->unique('id');
            }
        } else {
            $organization = $this->organizationRepository->find($localityId, ['agency', 'stores']);
            $divisionId   = [$organization->parent_id];

            list('agencies' => $agencies, 'stores' => $stores) = $this->_parseOptions($organization->agency, $organization->stores);
        }
        $storeId = $defaultValues['store_id'] ?? 0;
        $store   = $storeId ? $this->storeRepository->find($storeId) : null;
        // PRODUCT TYPE
        $product_types      = ['' => '- Không có lựa chọn -'];
        $firstProductTypeId = (int)($defaultValues['product_type'] ?? 0);
        if ($storeId && $store) {
            $product_types = [];
            foreach ($this->reportRevenueStoreRankService->getProductTypesFromStoreType($store->type) as $productTypeId) {
                $product_types[$productTypeId] = 'Loại ' . ProductGroup::PRODUCT_TYPES[$productTypeId]['text'];
            }
            if (!$firstProductTypeId || !isset($product_types[$firstProductTypeId])) {
                $firstProductTypeId = collect($product_types)->keys()->first();
            }
        }
        // PRODUCT - Lay san pham theo NHOM SP cua USER
        $currentDate             = now()->format('Y-m-d');
        $searchProductPriorities = ['minDate' => $currentDate, 'maxDate' => $currentDate, 'store_type' => $store?->type ?: ''];
        $productPriorities       = $this->productGroupPriorityRepository->getList([], $searchProductPriorities);
        $productPriorities       = collect($productPriorities);
        if ($currentUser->product_groups->isNotEmpty()) {
            $product_ids = $productPriorities->whereIn('group_id', $currentUser->product_groups->pluck('id')->toArray())
                ->filter(function ($item) use ($firstProductTypeId) {
                    return $item->product_type == $firstProductTypeId;
                })
                ->pluck('product_id')->toArray();
        } else {
            $product_ids = $productPriorities
                ->filter(function ($item) use ($firstProductTypeId) {
                    return $item->product_type == $firstProductTypeId;
                })
                ->pluck('product_id')->toArray();
        }
        $products        = $this->productRepository->getByArrId($product_ids)
            ->map(function ($item) {
                $item->ascii_name = Str::ascii($item->name);
                return $item;
            })
            ->sortBy('ascii_name')->values();
        $product_options = view('pages.store_orders.product-options', compact('products'))->render();
        // PROMOTION
        $promotions       = $this->promotionRepository->getByRequest(50, ['promotionConditions'], [
            'division_id' => $divisionId,
            'status'      => Promotion::STATUS_ACTIVE,
            'date'        => now()->format('Y-m-d'),
        ]);
        $promotions       = $promotions->getCollection()->sortByDesc('auto_apply');
        $promotion_values = $defaultValues['promotions'] ?? [];
        if (!$promotion_values) {
            foreach ($promotions as $promotion) {
                if ($promotion->auto_apply == Promotion::AUTO_APPLY) {
                    $order = [];
                    foreach ($promotion->promotionConditions as $_i => $promotionCondition) {
                        $order[$_i + 1] = $promotion->id . '_' . $promotionCondition->id;
                    }
                    $promotion_values[$promotion->id] = json_encode($order);
                }
            }
        }
        $promotion_checked = collect($promotion_values)->map(function ($item) {
            return collect(json_decode($item, true))->map(function ($item) {
                $tmp = explode('_', $item);
                return $tmp[1] ?? false;
            })->filter()->values()->toArray();
        })->toArray();
        $promotion_view    = $storeId ? view('pages.store_orders.promotions', compact('promotions', 'promotion_values', 'promotion_checked'))->render() : '';
        // TRA THUONG KEY
        // GIA TRI MAC DINH
        $order_types            = [StoreOrder::ORDER_TYPE_DON_THUONG => StoreOrder::ORDER_TYPE_TEXTS[StoreOrder::ORDER_TYPE_DON_THUONG]];
        $order_logistics        = ['' => '- Không có lựa chọn -'];
        $order_bonus_info       = '';
        $order_logistic_default = '';
        // KIEM TRA TRA THUONG KEY
        $validTTKey = $storeId ? $this->validTTKey($storeId, $firstProductTypeId) : null;

        if ($storeId && $validTTKey) {
            if (isset($validTTKey['order_types'])) {
                $order_types = $validTTKey['order_types'];
            }
            if (isset($validTTKey['order_logistics'])) {
                $order_logistics = $validTTKey['order_logistics'];
            }
            if (isset($validTTKey['bonus_info'])) {
                $order_bonus_info = $validTTKey['bonus_info'];
            }
            if (empty($defaultValues['order_logistic'])) {
                $order_logistic_default = $currentUser->id;
            }
            if (in_array($validTTKey['rank_name'], ['Vàng', 'Super'])) {
                $order_logistic_default = StoreOrder::ORDER_LOGISTIC_VIETTEL;
            }
        }

        // MESSAGE
        $messages = [];
        if ($localityId && $agencies->isEmpty()) {
            $messages[] = 'Không tìm thấy đại lý';
        }
        if ($localityId && $stores->isEmpty()) {
            $messages[] = 'Không tìm thấy nhà thuốc';
        }
        $show_product = $localityId && (
                ($storeId && ($defaultValues['agency_id'] ?? 0)) || ($storeId && $order_bonus_info)
            );

        return compact('products', 'agencies', 'stores', 'promotions', 'promotion_view', 'promotion_values',
            'messages', 'order_types', 'order_logistics', 'order_bonus_info', 'order_logistic_default', 'product_types', 'product_options',
            'show_product',
//            'validTTKey',
        );
    }

    public function validTTKey($storeId, $productType): array
    {
        $store       = $this->storeRepository->find($storeId, ['organization', 'organization.users']);
        $orderTTKey  = $this->repository->getOrderTTKey($storeId, $productType);
        $bonusDetail = $this->reportRevenueStoreRankService->getCurrentBonusOfStore($storeId, $store->type, $productType);
        $totalBonus  = $bonusDetail['total_bonus'] ?? 0;
        if ($totalBonus > $orderTTKey['total_amount'] && $totalBonus) {
            $order_types     = [
                StoreOrder::ORDER_TYPE_DON_THUONG => StoreOrder::ORDER_TYPE_TEXTS[StoreOrder::ORDER_TYPE_DON_THUONG],
                StoreOrder::ORDER_TYPE_DON_TTKEY  => StoreOrder::ORDER_TYPE_TEXTS[StoreOrder::ORDER_TYPE_DON_TTKEY],
            ];
            $order_logistics = [
                StoreOrder::ORDER_LOGISTIC_VIETTEL => 'Viettel',
            ];
            $rank_name       = $bonusDetail['rank_name'];
            if ($store->type == Store::STORE_TYPE_LE) {
                foreach ($store->organization?->users ?? [] as $_user) {
                    $order_logistics[$_user->id] = $_user->username . ' - ' . $_user->name;
                }
            }
            $bonus_info = view('pages.store_orders.bonus-info', ['items' => $bonusDetail['item_info'], 'total_bonus' => $totalBonus])->render();

            return compact(
                'order_types',
                'order_logistics',
                'bonus_info',
                'rank_name',
//                'bonusDetail',
            );
        }
        return [];
    }

    public function getPromotionValues($attributes): array
    {
        $productValues   = collect($attributes['products'] ?? [])
            ->filter(function ($item) {
                return $item['type'] == 'product' && intval($item['qty']);
            })
            ->map(function ($item) {
                $item['qty_price'] = [
                    'price' => (int)$item['price'],
                    'qty'   => (int)$item['qty'],
                    'point' => (int)$item['point'],
                ];
                return $item;
            })
            ->pluck('qty_price', 'id')
            ->toArray();
        $promotionValues = collect($attributes['promotions'] ?? [])
            ->map(function ($item) {
                $item = collect(json_decode($item, true) ?? [])
                    ->map(function ($item) {
                        $item = explode('_', $item);
                        return $item[1] ?? false;
                    })
                    ->filter();

                return $item->isEmpty() ? false : $item->sortKeys();
            })
            ->filter();

        $promoItems = [];
        foreach ($promotionValues as $promoId => $conditionIds) {
            foreach ($conditionIds as $conditionId) {
                $promoItems[] = [
                    'promo_id'     => $promoId,
                    'condition_id' => $conditionId,
                    'data'         => $this->promotionItems($promoId, $conditionId, $productValues),
                ];
            }
        }

        return $promoItems;
    }

    /**
     * @param $promoId
     * @param $conditionId
     * @param array $productValues
     * @return array
     */
    public function promotionItems($promoId, $conditionId, array $productValues): array
    {
        $output = [
            'message' => [],
            'items'   => [],
            'type'    => 'success',
        ];

        $promotionValues = $this->promotionService->calculatePromotion($promoId, $conditionId, $productValues);
        if (!$promotionValues) {
            $output['message'][] = 'Chương trình KM có thay đổi, không áp dụng được KM này. Liên hệ với SA để biết thêm chi tiết.';
            $output['type']      = 'warning';
            $output['changed']   = true;
            return $output;
        }
        if (
            ($promotionValues['type'] == 'discount' && !$promotionValues['discounts'])
            || ($promotionValues['type'] == 'gift' && !$promotionValues['gifts'])
        ) {
            $output['message'][] = 'Số lượng sản phẩm không đủ để áp dụng điều kiện KM này.';
            $output['type']      = 'warning';
            return $output;
        }

        if ($promotionValues['type'] == 'gift' && $promotionValues['gifts']) {
            $gifts = $this->giftRepository->getByArrId(array_keys($promotionValues['gifts']));
            foreach ($promotionValues['gifts'] as $giftId => $qty) {
                $gift                  = $gifts->where('id', $giftId)->first();
                $key                   = sprintf('gift_%s_%s_%s', $promoId, $conditionId, $giftId);
                $output['items'][$key] = [
                    'key'            => $key,
                    'promo_id'       => $promoId,
                    'condition_id'   => $conditionId,
                    'promo_name'     => $promotionValues['promotion_name'],
                    'condition_name' => $promotionValues['condition_name'],
                    'name'           => $gift->name,
                    'type'           => 'gift',
                    'id'             => $giftId,
                    'qty'            => $qty,
                    'price'          => $gift->price,
                    'point'          => 0,
                    'amount'         => 0,
                    'price_format'   => Helper::formatPrice($gift->price),
                    'amount_format'  => 0,
                    'sort'           => 3,
                    'details'        => '',
                ];
                $output['message'][]   = sprintf('%s x <b>%s</b>', $gift->name, $qty);
            }
        } elseif ($promotionValues['type'] == 'discount' && $promotionValues['discounts']) {
            if (isset($promotionValues['discounts']['products']) || isset($promotionValues['discounts']['orders'])) {
                $output['discount_products'][] = $promotionValues['discounts']['products'] ?? [];

                $discountProduct = $discountOrder = 0;
                foreach ($promotionValues['discounts']['products'] ?? [] as $values) {
                    $discountProduct += collect($values['details'])->sum('discount');
                }
                foreach ($promotionValues['discounts']['orders'] ?? [] as $values) {
                    $discountOrder += collect($values)->sum('discount');
                }

                foreach (['product' => $discountProduct, 'order' => $discountOrder] as $_type => $_amount) {
                    if (!$_amount) {
                        continue;
                    }
                    $key                   = sprintf('discount_%s_%s_%s', $_type, $promoId, $conditionId);
                    $output['items'][$key] = $this->_outputDiscountItem($key, $promoId, $conditionId, $promotionValues, $_amount);
                }
                $output['message'][] = sprintf('Chiết khấu:  <b>%s</b>', Helper::formatPrice($discountProduct + $discountOrder));
            } else {
                $key                   = sprintf('discount_%s_%s', $promoId, $conditionId);
                $amount                = array_sum($promotionValues['discounts']);
                $output['items'][$key] = $this->_outputDiscountItem($key, $promoId, $conditionId, $promotionValues, $amount);
                $output['message'][]   = sprintf('Chiết khấu:  <b>%s</b>', $output['items'][$key]['amount_format']);
            }
        }

        $output['items'] = array_map([$this, '_checkItemValue'], $output['items']);


        return $output;
    }

    function _outputDiscountItem($key, $pid, $cid, $values, $amount): array
    {
        return [
            'key'            => $key,
            'promo_id'       => $pid,
            'condition_id'   => $cid,
            'promo_name'     => $values['promotion_name'],
            'condition_name' => $values['condition_name'],
            'name'           => $values['condition_name'],
            'type'           => 'discount',
            'id'             => 0,
            'qty'            => 1,
            'price'          => $amount,
            'point'          => 0,
            'amount'         => $amount,
            'price_format'   => '',
            'amount_format'  => Helper::formatPrice($amount),
            'sort'           => 4,
            'details'        => json_encode($values['discounts']['products']) ?: '',
        ];
    }

    public function _checkItemValue(array $attributes): array
    {
        if (!isset($attributes['amount'])) {
            if ($attributes['type'] == 'discount') {
                $attributes['amount'] = $attributes['price'];
            } elseif ($attributes['type'] == 'gift') {
                $attributes['amount'] = 0;
            } elseif ($attributes['type'] == 'product') {
                $attributes['amount'] = $attributes['qty'] * $attributes['price'];
            }
        }
        if (!isset($attributes['price_format'])) {
            $attributes['price_format'] = '';
            if (in_array($attributes['type'], ['product', 'gift'])) {
                $attributes['price_format'] = Helper::formatPrice($attributes['price']);
            }
        }
        if (!isset($attributes['amount_format'])) {
            if ($attributes['type'] == 'gift') {
                $attributes['amount_format'] = 0;
            } else {
                $attributes['amount_format'] = Helper::formatPrice($attributes['amount']);
            }
        }
        if (!isset($attributes['sort'])) {
            if ($attributes['type'] == 'discount') {
                $attributes['sort'] = 4;
            } elseif ($attributes['type'] == 'gift') {
                $attributes['sort'] = 3;
            } elseif ($attributes['type'] == 'product' && $attributes['promo_id']) {
                $attributes['sort'] = 1;
            } else {
                $attributes['sort'] = 2;
            }
        }
        foreach (['promo_id', 'condition_id', 'id', 'qty', 'price', 'point'] as $key) {
            if (isset($attributes[$key])) {
                $attributes[$key] = (int)$attributes[$key];
            }
        }

        return $attributes;
    }

    public function formOptionsForAgencyTDVOrder($model = null): array
    {
        $options                 = parent::formOptions($model);
        $options['divisions']    = $this->organizationRepository->getDivisionsActive();
        $options['locality_ids'] = isset($model)
            ? $this->organizationRepository->getLocalityActive()
            : [];

        if (request()->route()->getName() == 'admin.agency.index') {
            $options['locality_ids'] = $this->organizationRepository->getLocalityByDivision(request('search.division_id', null));
        }

        $options['booking_at'] = $this->handleDateRangeData(request('search.booking_at', null));

        $options['areas'] = [];

        return $options;
    }

    public function getTableForAgencyTDVOrder($requestParams = [], $showOption = [])
    {
        $showOption = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [[
                "column" => "store_orders.created_at",
                "type"   => "DESC"
            ]]
        ], $showOption);

        $requestParams['booking_at']    = $this->handleDateRangeData($requestParams['booking_at'] ?? '');
        $requestParams['agency_status'] = $requestParams['agency_status'] ?? StoreOrder::AGENCY_STATUS_CHUA_THANH_TOAN;

        if (isset($requestParams['agencyCode'])) {
            $requestParams = ['agencyCode' => $requestParams['agencyCode']];
        }

        $storeOrders = $this->repository->getDataForListScreen(
            with: [
                'items',
                'agency.province',
                'items.product',
                'sale',
                'store',
                'store.organization',
                'items.gift',
                'agencyOrder',
                'agencyOrder.creator'
            ],
            requestParams: $requestParams,
            showOption: $showOption
        );

        $storeOrders->map(function ($storeOrder) {
            $itemTexts               = [];
            $storeOrder->agency_info = $storeOrder->agency_name . ($storeOrder->agency?->province ? (" - " . $storeOrder->agency?->province?->province_name) : '');
            $storeOrder->store_info  = $storeOrder->store?->name . ($storeOrder->organization ? (" - " . $storeOrder->organization?->name) : '');

            foreach ($storeOrder->items as $storeOrderItem) {
                if ($storeOrderItem->product_type != StoreOrderItem::PRODUCT_TYPE_DISCOUNT) {
                    $productName = '';
                    if ($storeOrderItem->product_type == StoreOrderItem::PRODUCT_TYPE_PRODUCT) {
                        $productName = $storeOrderItem->product?->code ?? $storeOrderItem->product?->name;
                    }

                    if ($storeOrderItem->product_type == StoreOrderItem::PRODUCT_TYPE_GIFT) {
                        $productName = $storeOrderItem->gift?->code ?? $storeOrderItem->gift?->name;
                    }

                    $productQty   = $storeOrderItem->product_qty;
                    $productPrice = Helper::formatPrice($storeOrderItem->product_price);

                    $itemTexts[] = "<div class='d-flex' style='font-size: 13px; line-height: 1.5;'>
                        <b class='me-1 text-nowrap text-primary'>$productName</b>
                        <span class='ms-auto text-nowrap'>$productQty * $productPrice</span>
                    </div>";
                }
            }

            $storeOrder->item_texts = implode('', $itemTexts);

            $storeOrder->sale_name             = isset($storeOrder->sale)
                ? $storeOrder->sale?->name
                : '';
            $storeOrder->create_and_booking_at = (Carbon::create($storeOrder->booking_at)?->format('d/m/Y') ?? "***");
            $storeOrder->create_and_booking_at .= $storeOrder->agency_status == StoreOrder::AGENCY_STATUS_DA_THANH_TOAN
                ? (' <br> ' . Carbon::create($storeOrder->agencyOrder->booking_at)->format('d/m/Y')) : '<br>***';
            $storeOrder->status                = match ($storeOrder->status) {
                StoreOrder::STATUS_CHUA_GIAO => '<div class="badge badge-light-primary rounded-3" style="padding: 5px 10px">' . StoreOrder::STATUS_TEXTS[StoreOrder::STATUS_CHUA_GIAO] . '</div>',
                StoreOrder::STATUS_DA_GIAO => '<div class="badge badge-light-success rounded-3" style="padding: 5px 10px">' . StoreOrder::STATUS_TEXTS[StoreOrder::STATUS_DA_GIAO] . '</div>',
                default => '<div class="badge badge-light-danger rounded-3" style="padding: 5px 10px">Không có</div>'
            };
            if ($storeOrder->status) {
                $storeOrder->status .= '<br>';
            }
            $storeOrder->status       .= match ($storeOrder->agency_status) {
                StoreOrder::AGENCY_STATUS_CHUA_THANH_TOAN => '<div class="badge badge-light-primary rounded-3" style="padding: 5px 10px; margin-top: 5px">' . StoreOrder::AGENCY_STATUS_TEXT[StoreOrder::AGENCY_STATUS_CHUA_THANH_TOAN] . '</div>',
                StoreOrder::AGENCY_STATUS_DA_THANH_TOAN => '<div class="badge badge-light-success rounded-3" style="padding: 5px 10px; margin-top: 5px">' . StoreOrder::AGENCY_STATUS_TEXT[StoreOrder::AGENCY_STATUS_DA_THANH_TOAN] . '</div>',
                default => '<div class="mt-1 badge badge-light-danger rounded-3" style="padding: 5px 10px">Không có</div>'
            };
            $storeOrder->features     = "";
            $storeOrder->total_amount = Helper::formatPrice($storeOrder->total_amount);
            $storeOrder->discount     = Helper::formatPrice($storeOrder->discount);
            if (isset($storeOrder->agencyOrder?->creator)) {
                $storeOrder->order_code = '<b class="text-primary">' . $storeOrder->order_code . '</b></br> (' . $storeOrder->agencyOrder?->creator?->name . ')';
            }
            $storeOrder->code = '<b class="text-primary">' . $storeOrder->code . '</b>';

            if ($storeOrder->agency?->code) {
                $storeOrder->checkbox = "<input type='checkbox' value='" . $storeOrder->id
                    . "' class='form-check-input select-agency-tdv-order check-allow-create-order' name='ids[]'>";
            }

            return $storeOrder;
        });

        return new TableHelper(
            collections: $storeOrders,
            nameTable: 'agency-tdv-order-list',
        );
    }

    public function export($hash_id, $requestParams, $showOption, $fileDir = 'agency_order')
    {
        $requestParams['booking_at']    = $this->handleDateRangeData($requestParams['booking_at'] ?? '');
        $requestParams['agency_status'] = $requestParams['agency_status'] ?? StoreOrder::AGENCY_STATUS_CHUA_THANH_TOAN;

        $query = $this->repository->getQueryExportListScreen(
            with: ['items', 'agency.division', 'items.product', 'sale'],
            requestParams: $requestParams,
            showOption: $showOption
        );

        $file_name   = 'agency_tdv_order' . '_' . Carbon::now()->timestamp . "_.csv";
        $export_data = request()->get('export_agency_tdv_order_data', cache()->get($hash_id)) ?: [];

        $export_options = [
            'hash_id'   => $hash_id,
            'file_name' => $file_name,
            'file_dir'  => $fileDir,
            'headers'   => [
                "STT",
                "Mã đơn NT",
                "Mã đơn TT",
                "TDV",
                "Đại lý",
                "Khu vực",
                "Địa chỉ",
                "Ngày cập nhập/Ngày nhập hàng",
                "Trạng thái",
                "Sản phẩm",
                "Tổng tiền",
            ],
            'limit'     => 500,
        ];

        if (!$export_data) {
            $export_options['total'] = $query->getQuery()->getCountForPagination();
        }

        $exportService = new ExportService($export_data, $query, $export_options);

        $countAgencyOrder = 1;
        $export_data      = $exportService->exportProgress(function ($storeOrder, $key) use (&$countAgencyOrder) {
            $itemTexts = [];

            foreach ($storeOrder->items as $storeOrderItem) {
                $itemTexts[] = ($storeOrderItem->product->name ?? '') . ' ' . $storeOrderItem->product_qty
                    . ' X ' . $storeOrderItem->product_price;
            }
            $result = [
                $countAgencyOrder,
                $storeOrder->code,
                $storeOrder->order_code,
                $storeOrder->sale?->name,
                $storeOrder->agency_name,
                $storeOrder->agency?->division?->first()?->name,
                $storeOrder->fullAddress,
                ($storeOrder->updated_at ?? "***") . ' - ' . ($storeOrder->booking_at ?? "***"),
                match ($storeOrder->status) {
                    StoreOrder::STATUS_CHUA_GIAO => StoreOrder::STATUS_TEXTS[StoreOrder::STATUS_CHUA_GIAO],
                    StoreOrder::STATUS_DA_GIAO => StoreOrder::STATUS_TEXTS[StoreOrder::STATUS_DA_GIAO],
                    default => ''
                },
                implode(', ', $itemTexts),
                $storeOrder->total_amount
            ];
            $countAgencyOrder++;

            return $result;
        });

        return response()->json($export_data);
    }

    public function isAllowToCreateOrder($storeOrderIds)
    {
        $orderCreatedByTDV = $this->repository->getStoreOrderPayed($storeOrderIds);

        if (count($orderCreatedByTDV)) {
            return false;
        }

        return true;
    }

    public function getAgencyStoreOrder($storeOrderIds)
    {
        $result      = [];
        $storeOrders = $this->repository->getByArrId($storeOrderIds, [
            'agency',
            'agency.localies',
            'items',
            'sale',
            'items.product'
        ]);

        foreach ($storeOrders as $storeOrder) {
            if (isset($storeOrder->agency?->name)) {
                if (isset($result[$storeOrder->agency?->name])) {
                    $result[$storeOrder->agency?->name]['orders'][] = $storeOrder;
                } else {
                    $result[$storeOrder->agency?->name]['orders']               = [$storeOrder];
                    $result[$storeOrder->agency?->name]['agency']               = $storeOrder->agency;
                    $localies                                                   = $storeOrder->agency?->localies?->pluck("name")->toArray() ?? [];
                    $result[$storeOrder->agency?->name]['agency']->localiesName = implode(", ", $localies);
                }
            }
        }

        return $result;
    }

    protected function getAmountForProductAndGroup($collectionAmount, $userId = null)
    {
        $result = [];

        if ($userId) {
            $collectionAmount = $collectionAmount->filter(function ($amount) use ($userId) {
                return $amount->user?->id == $userId;
            });
        }

        foreach ($collectionAmount as $productAmount) {
            if (isset($productAmount->productGroup) && isset($productAmount->product)) {

                $productGroupName = $productAmount->productGroup?->name;
                $productName      = $productAmount->product?->name;
                $sumTotalAmount   = $productAmount->sum_total_amount ?? 0;

                $result[$productGroupName]['items'][] = [
                    'name'               => $productName,
                    'month_total_amount' => $sumTotalAmount
                ];

                if (isset($result[$productGroupName]['month_total_amount'])) {
                    $result[$productGroupName]['month_total_amount'] += $sumTotalAmount;
                } else {
                    $result[$productGroupName]['month_total_amount'] = $sumTotalAmount;
                }
            }
        }

        return $result;
    }

    public function getDataTurnover($storeId, $dataRequest)
    {
        $currentDate       = Carbon::now();
        $currentUser       = Helper::currentUser();
        $month             = $dataRequest['month'] ?? $currentDate->month;
        $year              = $dataRequest['year'] ?? $currentDate->year;
        $previousMonth     = Helper::getMonthPrevious($year, $month);
        $previousMonthText = $previousMonth['year'] . '-' . str_pad($previousMonth['month'], 2, "0", STR_PAD_LEFT);
        $currentRevenue    = Helper::getCurrentRevenue("$year-$month-01");
        $previousRevenue   = Helper::getPreviousRevenue("$year-$month-01");
        $textMonth         = str_pad($month, 2, "0", STR_PAD_LEFT);

        $currentMonthCollectionAmount    = $this->reportRevenueOrderRepository->getByDateRange(
            "$year-$textMonth-01",
            "$year-$textMonth-31",
            $storeId
        );
        $previousMonthCollectionAmount   = $this->reportRevenueOrderRepository->getByDateRange(
            "$previousMonthText-01",
            "$previousMonthText-31",
            $storeId
        );
        $currentRevenueCollectionAmount  = $this->reportRevenueOrderRepository->getByDateRange(
            $currentRevenue['from'],
            $currentRevenue['to'],
            $storeId
        );
        $previousRevenueCollectionAmount = $this->reportRevenueOrderRepository->getByDateRange(
            $previousRevenue['from'],
            $previousRevenue['to'],
            $storeId
        );

        $totalAmountAllUserMonth           = $currentMonthCollectionAmount->sum('sum_total_amount');
        $totalAmountAllUserPreviousMonth   = $previousMonthCollectionAmount->sum('sum_total_amount');
        $totalAmountAllUserRevenue         = $currentRevenueCollectionAmount->sum('sum_total_amount');
        $totalAmountAllUserPreviousRevenue = $previousRevenueCollectionAmount->sum('sum_total_amount');
        $previousRevenue                   = sprintf(
            "%s -> %s",
            Carbon::create($previousRevenue['from'])->format('Y-m'),
            Carbon::create($previousRevenue['to'])->format('Y-m')
        );

        $totalAmountAllUser = [
            'month'   => [
                'current'       => $totalAmountAllUserMonth,
                'previous'      => $totalAmountAllUserPreviousMonth,
                'previousMonth' => $previousMonth['month'],
                'percent'       => Helper::calculatePercent($totalAmountAllUserMonth, $totalAmountAllUserPreviousMonth)
            ],
            'revenue' => [
                'current'         => $totalAmountAllUserRevenue,
                'previous'        => $totalAmountAllUserPreviousRevenue,
                'previousRevenue' => $previousRevenue,
                'percent'         => Helper::calculatePercent($totalAmountAllUserRevenue, $totalAmountAllUserPreviousRevenue)
            ]
        ];

        $productAmountCurrentMonth = $this->reportRevenueOrderRepository->getByDateRange(
            "$year-$textMonth-01",
            "$year-$textMonth-31",
            $storeId
        );

        $groupMonthAllUser     = $this->getAmountForProductAndGroup($productAmountCurrentMonth);
        $groupMonthCurrentUser = $this->getAmountForProductAndGroup($productAmountCurrentMonth, $currentUser->id);

        $productAmountCurrentRevenue = $this->reportRevenueOrderRepository->getByDateRange(
            $currentRevenue['from'],
            $currentRevenue['to'],
            $storeId
        );
        $groupRevenueAllUser         = $this->getAmountForProductAndGroup($productAmountCurrentRevenue);
        $groupRevenueCurrentUser     = $this->getAmountForProductAndGroup($productAmountCurrentRevenue, $currentUser->id);

        return [
            'store'          => $this->storeRepository->find($storeId),
            'currentMonth'   => $month,
            'currentRevenue' => $currentRevenue,
            'month'          => [
                'totalAmount'   => $totalAmountAllUser['month'],
                'groups'        => $groupMonthCurrentUser,
                'groupsAllUser' => $groupMonthAllUser,
                'orders'        => [

                ]
            ],
            'revenue'        => [
                'totalAmount'   => $totalAmountAllUser['revenue'],
                'groups'        => $groupRevenueCurrentUser,
                'groupsAllUser' => $groupRevenueAllUser,
            ]
        ];
    }
}
