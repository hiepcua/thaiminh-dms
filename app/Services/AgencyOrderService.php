<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Helpers\TableHelper;
use App\Models\AgencyOrderFile;
use App\Models\AgencyOrderHistory;
use App\Models\AgencyOrderItem;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductGroupPriority;
use App\Models\PromotionCondition;
use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
use App\Repositories\Agency\AgencyRepositoryInterface;
use App\Repositories\AgencyOrder\AgencyOrderRepositoryInterface;
use App\Repositories\AgencyOrderItem\AgencyOrderItemRepositoryInterface;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ProductGroupPriority\ProductGroupPriorityRepositoryInterface;
use App\Repositories\StoreOrder\StoreOrderRepositoryInterface;
use App\Services\BaseService;
use App\Models\AgencyOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use mikehaertl\wkhtmlto\Pdf;

class AgencyOrderService extends BaseService
{
    protected $repository;
    protected $organizationRepository;
    protected $agencyRepository;
    protected $storeOrderService;
    protected $storeOrderRepository;
    protected $agencyOrderItemRepository;
    protected $productRepository;
    protected $productGroupPriorityRepository;
    protected $reportAgencyInventoryService;

    public function __construct(
        AgencyOrderRepositoryInterface          $repository,
        OrganizationRepositoryInterface         $organizationRepository,
        AgencyRepositoryInterface               $agencyRepository,
        StoreOrderService                       $storeOrderService,
        StoreOrderRepositoryInterface           $storeOrderRepository,
        AgencyOrderItemRepositoryInterface      $agencyOrderItemRepository,
        ProductRepositoryInterface              $productRepository,
        ProductGroupPriorityRepositoryInterface $productGroupPriorityRepository,
        ReportAgencyInventoryService            $reportAgencyInventoryService
    )
    {
        parent::__construct();

        $this->repository                     = $repository;
        $this->organizationRepository         = $organizationRepository;
        $this->agencyRepository               = $agencyRepository;
        $this->storeOrderService              = $storeOrderService;
        $this->storeOrderRepository           = $storeOrderRepository;
        $this->agencyOrderItemRepository      = $agencyOrderItemRepository;
        $this->productRepository              = $productRepository;
        $this->productGroupPriorityRepository = $productGroupPriorityRepository;
        $this->reportAgencyInventoryService   = $reportAgencyInventoryService;
    }

    public function setModel()
    {
        return new AgencyOrder();
    }

    public function getProductHasGrouped($bookingAt = [])
    {
        $products               = [];
        $productGroupPriorities = $this->productGroupPriorityRepository->getProductGroup([
            'from' => $bookingAt['from'] ?? now()->format('Y-m-d'),
            'to'   => $bookingAt['to'] ?? now()->format('Y-m-d'),
        ], priority: null);

        foreach ($productGroupPriorities as $productGroupPriority) {
            if ($productGroupPriority->productGroup && $productGroupPriority->product) {
                $products[$productGroupPriority->productGroup?->name][$productGroupPriority->product?->id] = $productGroupPriority->product;
            }
        }

        return $products;
    }

    public function formOptions($model = null): array
    {
        $options = parent::formOptions($model);
//        dd($options);
        $options['divisions']    = $this->organizationRepository->getDivisionsActive();
        $options['locality_ids'] = isset($model)
            ? $this->organizationRepository->getLocalityActive()
            : [];

        if (request()->route()->getName() == 'admin.agency.index') {
            $options['locality_ids'] = $this->organizationRepository->getLocalityByDivision(request('search.division_id', null));
        }

        $options['booking_at'] = $this->handleDateRangeData(request('search.booking_at', null));

        $options['areas'] = [];

        $options['products'] = $this->getProductHasGrouped([
            'from' => $options['booking_at']['from'] ?? now()->format('Y-m-d'),
            'to'   => $options['booking_at']['to'] ?? now()->format('Y-m-d'),
        ]);

        if (request()->route()->getName() == 'admin.agency-order.create') {
            $options['agencies'] = $this->agencyRepository->getByLocality(old('locality_id'));


            $products = $this->productRepository->getByArrId(array_keys(old('products') ?? []));
            foreach ((old('products') ?? []) as $key => $productQty) {
                $product                       = $products->firstWhere('id', $key);
                $options['old_products'][$key] = [
                    "name"        => $product?->name,
                    "qty"         => $productQty,
                    "price"       => $product?->price,
                    "totalAmount" => $productQty * $product?->price,
                ];
            }

            $options['default_values']['agency_id']   = old('agency_id');
            $options['default_values']['locality_id'] = old('locality_id');
        } else {
            if (isset($model)) {
                $options['agencies'] = [
                    $model->agency_id => $model->agency?->name
                ];
                $agencyOrderItems    = $model->agencyOrderItems;
                foreach ($agencyOrderItems as $product) {
                    $options['old_products'][$product->product_id] = [
                        "name"        => $product?->name,
                        "qty"         => $product->product_qty,
                        "price"       => $product?->price,
                        "totalAmount" => $product?->total_amount,
                    ];
                }

                $options['default_values']['agency_id'] = $model->agency_id;
            }
        }


        return $options;
    }

    public function getTable($requestParams = [], $showOption = [])
    {
        $showOption = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [[
                "column" => "agency_orders.created_at",
                "type"   => "DESC"
            ]]
        ], $showOption);

        $requestParams['booking_at'] = $this->handleDateRangeData($requestParams['booking_at'] ?? '');

        $agencyOrders = $this->repository->getDataForListScreen(
            with: [
                'agencyOrderItems',
                'agency.division',
                'agency.localies',
                'agencyOrderItems.gift',
                'agencyOrderItems.product'
            ],
            requestParams: $requestParams,
            showOption: $showOption
        );

        $currentUser          = Helper::currentUser();
        $canDeleteAgencyOrder = $currentUser->can('xoa_don_nhap_dai_ly');
        $canEditAgencyOrder   = $currentUser->can('sua_don_nhap_dai_ly');
        $canShowAgencyOrder   = $currentUser->can('xem_chi_tiet_don_nhap_dai_ly');

        $agencyOrders->map(function ($agencyOrder) use ($canDeleteAgencyOrder, $canEditAgencyOrder, $canShowAgencyOrder) {
            $itemTexts        = [];
            $totalDiscount    = 0;
            $agencyOrderItems = $agencyOrder->agencyOrderItems->sortBy([
                ['product_type', 'desc'],
                ['product_name', 'asc'],
            ]);

            foreach ($agencyOrderItems as $agencyOrderItem) {
                $totalDiscount += $agencyOrderItem->discount;

                if ($agencyOrderItem->product_type != AgencyOrderItem::PRODUCT_TYPE_DISCOUNT) {
                    $productCode  = $agencyOrderItem->product_type == AgencyOrderItem::PRODUCT_TYPE_PRODUCT
                        ? $agencyOrderItem->product?->code
                        : $agencyOrderItem->gift?->code;
                    $productQty   = $agencyOrderItem->product_qty;
                    $productPrice = Helper::formatPrice($agencyOrderItem->product_price);

                    $itemTexts[] = "
                        <div class='d-flex' style='font-size: 13px; line-height: 1.5;'>
                            <b class='text-primary'>$productCode</b>
                            <span class='ms-auto text-nowrap'>$productQty * $productPrice</span>
                        </div>";
                }
            }
            $agencyOrder->item_texts     = implode('', $itemTexts);
            $agencyOrder->total_discount = Helper::formatPrice($totalDiscount) ?? 0;

            $address                   = $agencyOrder->fullAddress;
            $agencyOrder->address_info = $address ? "Địa chỉ: $address" : '';
            $organizations             = $agencyOrder->agency?->organizations;
            $divisions                 = [];

            foreach ($organizations as $organization) {
                $parent = $organization->parent;
                if (!$parent) {
                    continue;
                }

                $divisions = array_merge_recursive($divisions, [$parent->name => [$organization->name]]);
            }

            foreach ($divisions as $divisionName => $localities) {
                if ($agencyOrder->address_info) {
                    $agencyOrder->address_info .= '<br>';
                }
                $agencyOrder->address_info .= "<b class='text-primary'>$divisionName:</b> " . implode(', ', $localities);
            }

            $agencyId                           = $agencyOrder->agency?->id;
            $routeShowAgency                    = route('admin.agency.show', $agencyId);
            $agencyOrder->agency_name           = "<a href='$routeShowAgency'> $agencyOrder->agency_name </a>";
            $agencyOrder->division_name         = implode(', ', $agencyOrder->agency?->localies?->pluck('name')->toArray() ?? []);
            $agencyOrder->update_and_booking_at = ($agencyOrder->updated_at ?? "***") . ' - ' . ($agencyOrder->booking_at ?? "***");

            $agencyOrder->features = "";
            if ($canEditAgencyOrder
                && $agencyOrder->type == AgencyOrder::TYPE_AGENCY_ORDER
                && $agencyOrder->status == AgencyOrder::STATUS_CHUA_KC
            ) {
                $agencyOrder->features .= '<a class="btn btn-sm btn-icon"
                   href="' . route('admin.agency-order.edit', $agencyOrder->id) . '">
                    <i data-feather="edit" class="font-medium-2 text-body"></i>
                </a>';
            }
            $agencyOrder->status       = match ($agencyOrder->status) {
                AgencyOrder::STATUS_CHUA_KC => '<center><span class="badge badge-light-primary rounded-3 m-auto" style="padding: 5px 10px">' . AgencyOrder::STATUS_TEXTS[AgencyOrder::STATUS_CHUA_KC] . '</span></center>',
                AgencyOrder::STATUS_DA_KC => '<center><span class="badge badge-light-success rounded-3 m-auto" style="padding: 5px 10px">' . AgencyOrder::STATUS_TEXTS[AgencyOrder::STATUS_DA_KC] . '</span></center>',
                AgencyOrder::STATUS_HUY_DON => '<center><span class="badge badge-light-danger rounded-3 m-auto" style="padding: 5px 10px">' . AgencyOrder::STATUS_TEXTS[AgencyOrder::STATUS_HUY_DON] . '</span></center>',
            };
            $agencyOrder->total_amount = Helper::formatPrice($agencyOrder->total_amount);

            if ($canShowAgencyOrder) {
                $routeShowOrder        = $agencyOrder->type == AgencyOrder::TYPE_TDV_ORDER
                    ? route('admin.agency-order.show-order-tdv', $agencyOrder->id)
                    : route('admin.agency-order.show', $agencyOrder->id);
                $agencyOrder->features .= '<a class="btn btn-sm btn-icon"
                   href="' . $routeShowOrder . '">
                    <i data-feather="eye" class="font-medium-2 text-body"></i>
                </a>';
            }

            $agencyOrder->checkbox = "<input type='checkbox' value='" . $agencyOrder->id
                . "' class='form-check-input select-agency-order check-allow-remove-order' name='agencyOrder[]'>";
            $agencyOrder->type     = match ($agencyOrder->type) {
                AgencyOrder::TYPE_AGENCY_ORDER => '<span class="badge badge-light-info rounded-3" style="padding: 5px 10px">' . AgencyOrder::TYPE_TEXTS[AgencyOrder::TYPE_AGENCY_ORDER] . '</span>',
                AgencyOrder::TYPE_TDV_ORDER => '<span class="badge badge-light-warning rounded-3" style="padding: 5px 10px">'
                    . AgencyOrder::TYPE_TEXTS[AgencyOrder::TYPE_TDV_ORDER]
                    . '</span>'
                    . "<br><b class='text-primary'>$agencyOrder->order_code</b>",
            };

            $createAt               = $agencyOrder->created_at->format('d/m/Y');
            $bookingAt              = Carbon::create($agencyOrder->booking_at)->format('d/m/Y');
            $agencyOrder->date_info = "$createAt<br>$bookingAt";

            return $agencyOrder;
        });

        return new TableHelper(
            collections: $agencyOrders,
            nameTable: 'agency-order-list',
        );
    }

    public function export($hash_id, $requestParams, $showOption)
    {
        $requestParams['booking_at'] = $this->handleDateRangeData($requestParams['booking_at'] ?? '');
        $query                       = $this->repository->getQueryExportListScreen(
            with: ['agencyOrderItems', 'agency.division'],
            requestParams: $requestParams,
            showOption: $showOption
        );

        $file_name   = 'agency_order' . '_' . Carbon::now()->timestamp . "_.csv";
        $export_data = request()->get('export_agency_order_data', cache()->get($hash_id)) ?: [];

        $export_options = [
            'hash_id'   => $hash_id,
            'file_name' => $file_name,
            'file_dir'  => 'agency_order',
            'headers'   => [
                "STT",
                "Đại lý",
                "Khu vực",
                "Địa chỉ",
                "Ngày cập nhập/ Ngày nhập hàng",
                "Loại",
                "Trạng thái",
                "Ghi chú",
                "Sản phẩm",
                "Tổng tiền"
            ],
            'limit'     => 500,
        ];
        if (!$export_data) {
            $export_options['total'] = $query->getQuery()->getCountForPagination();
        }

        $exportService = new ExportService($export_data, $query, $export_options);

        $countAgencyOrder = 1;
        $export_data      = $exportService->exportProgress(function ($agencyOrder, $key) use (&$countAgencyOrder) {
            $itemTexts = [];

            foreach ($agencyOrder->agencyOrderItems as $agencyOrderItem) {
                $itemTexts[] = $agencyOrderItem->product?->name . ' ' . $agencyOrderItem->product_qty
                    . ' X ' . $agencyOrderItem->product_price;
            }

            $agencyOrder->item_texts = implode('', $itemTexts);

            $result = [
                $countAgencyOrder,
                $agencyOrder->agency_name,
                $agencyOrder->agency?->division?->first()?->name,
                $agencyOrder->agency_address,
                ($agencyOrder->updated_at ?? "***") . ' - ' . ($agencyOrder->booking_at ?? "***"),
                AgencyOrder::TYPE_TEXTS[$agencyOrder->type] ?? '',
                AgencyOrder::STATUS_TEXTS[$agencyOrder->status] ?? '',
                $agencyOrder->note,
                $agencyOrder->item_texts,
                $agencyOrder->total_amount
            ];

            $countAgencyOrder++;

            return $result;
        });

        return response()->json($export_data);
    }

    public function isAllowUpdateToDelete($agencyOrderIds)
    {
        $orderCreatedByTDV = $this->repository->getOrderCreatedByTDV($agencyOrderIds);
        $orderIsRemoved    = $this->repository->getOrderRemoved($agencyOrderIds);

        if (count($orderCreatedByTDV) || count($orderIsRemoved)) {
            return false;
        }

        $agencyOrders = $this->repository->getByArrId($agencyOrderIds);
        foreach ($agencyOrders as $order) {
            if ($order->status == AgencyOrder::STATUS_CHUA_KC) {
                return true;
            }
        }

        return false;
    }

    public function removeOrder($agencyOrderIds)
    {
        if ($this->isAllowUpdateToDelete($agencyOrderIds)) {
            $agencyOrders = $this->repository->getByArrId($agencyOrderIds, ['agencyOrderItems']);
            foreach ($agencyOrders as $agencyOrder) {
                $oldAgencyOrder = clone $agencyOrder;

                $agencyOrder->status     = AgencyOrder::STATUS_HUY_DON;
                $agencyOrder->updated_by = Helper::currentUser()?->id;
                $agencyOrder->save();

                AgencyOrderHistory::addAgencyOrderHistory($oldAgencyOrder, $agencyOrder);
            }

            return true;
        }

        return false;
    }

    public function storeByTdvOrder($storeOrderIds, $bookingAt)
    {
        try {
            DB::beginTransaction();
            if (!$this->storeOrderService->isAllowToCreateOrder($storeOrderIds)) {
                return false;
            }
            $pdfData = [];

            $storeOrders = $this->storeOrderRepository->getByArrId($storeOrderIds, [
                'agency',
                'agency.localies',
                'items',
                'items.productGroup',
                'sale',
                'items.product'
            ]);

            $storeOrdersGrouped = [];
            foreach ($storeOrders as $storeOrder) {
                if (isset($storeOrdersGrouped[$storeOrder->agency->id])) {
                    $storeOrdersGrouped[$storeOrder->agency->id][] = $storeOrder;
                } else {
                    $storeOrdersGrouped[$storeOrder->agency->id] = [$storeOrder];
                }
            };

            foreach ($storeOrdersGrouped as $storeOrderGroup) {
                $totalAmount = null;

                foreach ($storeOrderGroup as $storeOrder) {
                    $totalAmount += $storeOrder->total_amount;
                }
                $agency     = $storeOrderGroup[0]?->agency;
                $provinceId = $storeOrderGroup[0]?->store_province_id;
                $districtId = $storeOrderGroup[0]?->store_district_id;
                $wardId     = $storeOrderGroup[0]?->store_ward_id;
                $orderCode  = $this->getNewOrderCode($agency, $bookingAt);

                $newAgencyOrderAttributes = [
                    'title'              => $agency?->name . ' ' . now()->format('Y-m-d H:i:s'),
                    'booking_at'         => $bookingAt,
                    'total_amount'       => $totalAmount,
                    'agency_id'          => $agency?->id,
                    'agency_province_id' => $provinceId,
                    'agency_district_id' => $districtId,
                    'agency_ward_id'     => $wardId,
                    'agency_address'     => $agency?->address,
                    'note'               => '',
                    'type'               => AgencyOrder::TYPE_TDV_ORDER,
                    'status'             => AgencyOrder::STATUS_CHUA_KC,
                    'created_by'         => Helper::currentUser()->id,
                    'order_code'         => $orderCode
                ];

                $newAgencyOrder = $this->repository->create($newAgencyOrderAttributes);

                $newAgencyOrderFile = [
                    'agency_order_id'        => $newAgencyOrder->id,
                    'agency_id'              => $newAgencyOrder->agency_id,
                    'qty_store_order_merged' => count($storeOrderGroup),
                    'order_code'             => $newAgencyOrder->order_code,
                    'cost'                   => 0,
                    'discount'               => 0,
                    'final_cost'             => 0,
                    'item_qty'               => 0,
                ];

                $pdfData = [
                    'agencyOrder' => [],
                    'items'       => []
                ];

                $maxDateOfOrder = $minDateOfOrder = null;
                foreach ($storeOrderGroup as $storeOrder) {
                    if ($maxDateOfOrder == null || $storeOrder->booking_at > $maxDateOfOrder) {
                        $maxDateOfOrder = $storeOrder->booking_at;
                    }

                    if ($minDateOfOrder == null || $storeOrder->booking_at < $minDateOfOrder) {
                        $minDateOfOrder = $storeOrder->booking_at;
                    }

                    $storeOrder->agency_status   = StoreOrder::AGENCY_STATUS_DA_THANH_TOAN;
                    $storeOrder->agency_order_id = $newAgencyOrder->id ?? '';
                    $storeOrder->order_code      = $orderCode;
                    $storeOrder->save();
                    foreach ($storeOrder->items as $item) {
                        $promotionDetail = json_decode(json_encode($item->discount_detail), true);
                        $discountPercent = 0;
                        if (isset($promotionDetail[0])) {
                            $discountPercent = $promotionDetail[0]['percent'] ?? 0;
                        }

                        $newAgencyOrderFile['cost']          += $item->sub_total;
                        $newAgencyOrderFile['discount']      += $item->discount;
                        $newAgencyOrderFile['final_cost']    += $item->total_amount;
                        $newAgencyOrderFile['item_qty']      += $item->product_qty;
                        $agencyOrderItemData                 = [
                            "agency_order_id"      => $newAgencyOrder->id ?? '',
                            "product_type"         => $item->product_type ?? '',
                            "product_id"           => $item->product_id ?? '',
                            "product_group_id"     => $item->product_group_id ?? null,
                            "product_sub_group_id" => $item->product_sub_group_id ?? null,
                            "product_priority"     => $item->product_priority ?? null,
                            "product_name"         => $item->product_name ?? '',
                            "product_price"        => $item->product_price ?? '',
                            "product_qty"          => $item->product_qty ?? '',
                            "discount"             => $item->discount ?? '',
                            "sub_total"            => $item->sub_total ?? '',
                            "total_amount"         => $item->total_amount ?? '',
                            "note"                 => $item->note ?? '',
                            "discount_percent"     => $discountPercent
                        ];
                        $agencyOrderItemData["product_unit"] = Product::UNIT_TEXTS[$item->product?->unit] ?? '';
                        $this->agencyOrderItemRepository->create($agencyOrderItemData);
                        if ($item->product_type == StoreOrderItem::PRODUCT_TYPE_PRODUCT) {
                            $productGroupName = ProductGroup::PRODUCT_TYPES[$item->productGroup->product_type]['text'] ?? '';
                        } else {
                            $productGroupName = "Khuyến mãi";
                        }

                        if (isset($pdfData['productTypeInfo'][$productGroupName])) {
                            $pdfData['productTypeInfo'][$productGroupName]['qty']         += $agencyOrderItemData['product_qty'];
                            $pdfData['productTypeInfo'][$productGroupName]['amount']      += $agencyOrderItemData['sub_total'];
                            $pdfData['productTypeInfo'][$productGroupName]['discount']    += $agencyOrderItemData['discount'];
                            $pdfData['productTypeInfo'][$productGroupName]['totalAmount'] += $agencyOrderItemData['total_amount'];
                        } else {
                            $pdfData['productTypeInfo'][$productGroupName] = [
                                'qty'         => $agencyOrderItemData['product_qty'],
                                'amount'      => $agencyOrderItemData['sub_total'],
                                'discount'    => $agencyOrderItemData['discount'],
                                'totalAmount' => $agencyOrderItemData['total_amount'],
                            ];
                        }

                        $pdfData['items'][$productGroupName][] = $agencyOrderItemData;
                    }
                }

                $pdfData['items'] = array_merge(array_flip(array_column(ProductGroup::PRODUCT_TYPES, 'text')), $pdfData['items']);

                $pdfData['agencyOrder']         = $newAgencyOrderFile;
                $pdfData['agency']              = $agency->toArray();
                $pdfData['booking_at']          = Carbon::create($bookingAt)->format('d/m/Y');
                $pdfData['agent_agency_order']  = Helper::currentUser()->toArray();
                $pdfData['order_date_range']    = $maxDateOfOrder != $minDateOfOrder
                    ? Carbon::create($minDateOfOrder)->format('d/m') . ' - ' . Carbon::create($maxDateOfOrder)->format('d/m')
                    : Carbon::create($minDateOfOrder)->format('d/m');
                $fileName                       = 'agency_order_' . $newAgencyOrderFile['agency_order_id'] . "_" . now()->timestamp . '.html';
                $fileNamePdf                    = 'agency_order_' . $newAgencyOrderFile['agency_order_id'] . "_" . now()->timestamp . '.pdf';
                $newAgencyOrderFile['file_url'] = $fileNamePdf;
                AgencyOrderFile::create($newAgencyOrderFile);
                $htmlContent = View('pdfs.agency_order', compact('pdfData'));
                Storage::put('agency_order_files/' . $fileName, $htmlContent);
                $file = new Pdf(Storage::path('agency_order_files/' . $fileName));
                $file->saveAs(Storage::path('agency_order_files/' . $fileNamePdf));
                Storage::delete('agency_order_files/' . $fileName);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(__METHOD__ . " error:" . $e->getMessage());
            Log::error($e);

            return false;
        }
    }

    protected function getNewOrderCode($agency, $bookingAt)
    {
        $result      = $agency->order_code;
        $result      .= Carbon::create($bookingAt)->format('m.d');
        $bookingAt   = Carbon::create($bookingAt)->format('Y-m-d');
        $oldQty      = count($this->repository->getByAgencyOnMonth($agency->id, $bookingAt));
        $orderNumber = $oldQty >= 1 ? ('.' . ($oldQty + 1)) : '';

        return $result . $orderNumber . ($agency->type_tax ?? '');
    }

    public function storeAgencyOrder($data)
    {
        try {
            DB::beginTransaction();
            $items = $data['products'] ?? [];

            if (!isset($data['agency_id'])) {
                return false;
            }

            $agency = $this->agencyRepository->find($data['agency_id']);

            if (!$agency) {
                return false;
            }

            $products = $this->productRepository->getByArrId(array_keys($items), ['activeProductGroupPriority']);

            $totalAmountAgencyOrder = 0;
            foreach ($items as $key => $qty) {
                $qty     = (int)$qty;
                $product = $products->firstWhere('id', $key);
                if ($product && abs($qty) != 0) {
                    $totalAmountAgencyOrder += $product->price * $qty;
                }
            }
            $data = array_merge($data, [
                "total_amount"       => $totalAmountAgencyOrder,
                "agency_province_id" => $agency->province_id,
                "agency_address"     => $agency->address,
                "type"               => AgencyOrder::TYPE_AGENCY_ORDER,
                "status"             => AgencyOrder::STATUS_CHUA_KC,
                "created_by"         => Helper::currentUser()->id,
            ]);

            $newAgencyOrder = $this->model->create($data);

            foreach ($items as $key => $qty) {
                $product = $products->firstWhere('id', $key);
                if ($product && abs($qty) != 0) {
                    $totalAmount = $product->price * $qty;

                    $this->agencyOrderItemRepository->create([
                        'agency_order_id'      => $newAgencyOrder->id,
                        'product_type'         => AgencyOrderItem::PRODUCT_TYPE_PRODUCT,
                        'product_id'           => $product->id,
                        'product_group_id'     => $product->activeProductGroupPriority->group_id,
                        'product_sub_group_id' => $product->activeProductGroupPriority->sub_group_id,
                        'product_priority'     => $product->activeProductGroupPriority->priority,
                        'product_name'         => $product->name,
                        'product_price'        => $product->price,
                        'product_qty'          => $qty,
                        'discount'             => 0,
                        'sub_total'            => $totalAmount,
                        'total_amount'         => $totalAmount,
                        'note'                 => $data['note'] ?? '',
                        'created_by'           => Helper::currentUser()->id,
                    ]);

                    $bookingAt = Carbon::create($newAgencyOrder->booking_at);
                    $export = $qty < 0 ? abs($qty) : 0;
                    $import = $qty ?? 0;

                    $this->reportAgencyInventoryService->updateInventory(
                        $newAgencyOrder->agency_id,
                        $product->id,
                        $bookingAt->year,
                        $bookingAt->month,
                        $export,
                        $import
                    );
                }
            }
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(__METHOD__ . " error: " . $e->getMessage());
            Log::error($e);

            return false;
        }
    }

    public function getAgencyTdvOrder($id)
    {
        try {
            $order = $this->repository->find($id, [
                'agencyOrderItems',
                'agency',
                'creator',
                'agencyOrderItems.product',
                'agencyOrderItems.creator',
                'agency.localies'
            ]);

            if (!$order) {
                return [];
            }

            return [
                'agencyOrderCode' => $order->order_code,
                'agencyName'      => $order->agency?->name,
                'bookingAt'       => $order->booking_at ? Carbon::create($order->booking_at)->format('d/m/Y') : '',
                'localities'      => implode(", ", $order->agency?->localies?->pluck("name")->toArray() ?? []),
                'agencyAddress'   => $order->agency?->address,
                'items'           => $order->agencyOrderItems?->sortBy([
                    ['product_type', 'desc'],
                    ['product_name', 'asc'],
                ])->toArray()
            ];
        } catch (\Exception $e) {
            Log::error(__METHOD__ . " error: " . $e->getMessage());
            Log::error($e);

            return [];
        }
    }

    public function updateAgencyOrder($id, $data)
    {
        try {
            DB::beginTransaction();
            $agencyOrder    = $this->repository->find($id, ['agencyOrderItems']);
            $oldAgencyOrder = clone $agencyOrder;

            if ($agencyOrder->status != AgencyOrder::STATUS_CHUA_KC) {
                return false;
            }

            $items = $data['products'] ?? [];

            if (!isset($data['agency_id'])) {
                return false;
            }

            $agency = $this->agencyRepository->find($data['agency_id']);

            if (!$agency) {
                return false;
            }

            $products = $this->productRepository->getByArrId(array_keys($items), ['activeProductGroupPriority']);

            $totalAmountAgencyOrder = 0;
            foreach ($items as $key => $qty) {
                $qty     = (int)$qty;
                $product = $products->firstWhere('id', $key);
                if ($product && $qty != 0) {
                    $totalAmountAgencyOrder += $product->price * $qty;
                }
            }
            $data = array_merge($data, [
                "total_amount"       => $totalAmountAgencyOrder,
                "agency_province_id" => $agency->province_id,
                "agency_address"     => $agency->address,
                "updated_by"         => Helper::currentUser()->id,
            ]);

            $agencyOrder->update($data);
            $agencyOrder->agencyOrderItems()->delete();

            foreach ($items as $key => $qty) {
                $product = $products->firstWhere('id', $key);
                if ($product && $qty != 0
                    && $product->status == Product::STATUS_ACTIVE
                    && $product->activeProductGroupPriority
                ) {
                    $totalAmount = $product->price * $qty;

                    $this->agencyOrderItemRepository->create([
                        'agency_order_id'      => $id,
                        'product_type'         => AgencyOrderItem::PRODUCT_TYPE_PRODUCT,
                        'product_id'           => $product->id,
                        'product_group_id'     => $product->activeProductGroupPriority->group_id,
                        'product_sub_group_id' => $product->activeProductGroupPriority->sub_group_id,
                        'product_priority'     => $product->activeProductGroupPriority->priority,
                        'product_name'         => $product->name,
                        'product_price'        => $product->price,
                        'product_qty'          => $qty,
                        'discount'             => 0,
                        'sub_total'            => $totalAmount,
                        'total_amount'         => $totalAmount,
                        'note'                 => $data['note'] ?? '',
                        'created_by'           => Helper::currentUser()->id,
                    ]);
                }
            }

            AgencyOrderHistory::addAgencyOrderHistory($oldAgencyOrder, $agencyOrder);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(__METHOD__ . " error: " . $e->getMessage());
            Log::error($e);

            return false;
        }
    }
}
