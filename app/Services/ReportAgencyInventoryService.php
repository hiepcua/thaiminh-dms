<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Helpers\TableHelper;
use App\Models\ProductGroup;
use App\Repositories\Agency\AgencyRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ReportAgencyInventory\ReportAgencyInventoryRepositoryInterface;
use App\Services\BaseService;
use App\Models\ReportAgencyInventory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReportAgencyInventoryService extends BaseService
{
    protected $repository;
    protected $agencyRepository;
    protected $productRepository;

    public function __construct(ReportAgencyInventoryRepositoryInterface $repository,
                                AgencyRepositoryInterface                $agencyRepository,
                                ProductRepositoryInterface               $productRepository
    )
    {
        parent::__construct();

        $this->repository        = $repository;
        $this->agencyRepository  = $agencyRepository;
        $this->productRepository = $productRepository;
    }

    public function setModel()
    {
        return new ReportAgencyInventory();
    }

    public function indexFormOptions()
    {
        $productTypes       = ['' => '- Loại Hàng HT -'];
        $configProductTypes = ProductGroup::PRODUCT_TYPES;

        foreach ($configProductTypes as $key => $configProductType) {
            $productTypes[$key] = $configProductType['text'];
        }

        $inventoryStatuses = ['' => '- Trạng thái hàng tồn -'] + ReportAgencyInventory::STATUS_INVENTORY_TEXTS;

        return [
            'productTypes'      => $productTypes,
            'inventoryStatuses' => $inventoryStatuses
        ];
    }

    public function getTable($requestParams = [], $showOption = [])
    {
        $showOption   = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [[
                "column" => "agencies.created_at",
                "type"   => "DESC"
            ]]
        ], $showOption);
        $currentDate  = Carbon::now();
        $currentMonth = $currentDate->month;
        $currentYear  = $currentDate->year;

        $requestParams['month'] = $requestParams['month'] ?? $currentMonth;
        $requestParams['year']  = $requestParams['year'] ?? $currentYear;

        $productIds = [];
        if (isset($requestParams['product_type']) || isset($requestParams['codeOrNameProduct'])) {
            $productIds = $this->productRepository->getForSearchInventory(
                $requestParams['product_type'] ?? null,
                $requestParams['codeOrNameProduct'] ?? null
            )->pluck('id')->toArray();
        }
        $requestParams['product_ids'] = $productIds;

        $agencies = $this->agencyRepository->getInventoryHistory(
            $requestParams,
            ['reportAgencyInventory', 'reportAgencyInventory.product'],
            $showOption
        );

        $agencyIds = $agencies->getCollection()->pluck('id')->toArray();

        $reportAgencyInventories = $this->repository->getByAgencyIdsMonth(
            $agencyIds,
            ['product'],
            $requestParams
        );

        $agencyInventories = [];

        foreach ($reportAgencyInventories as $reportAgencyInventory) {
            if (!isset($agencyInventories[$reportAgencyInventory->agency_id])) {
                $agencyInventories[$reportAgencyInventory->agency_id] = [];
            }

            if ($reportAgencyInventory->product) {
                $agencyInventories[$reportAgencyInventory->agency_id][] = array_merge(
                    $reportAgencyInventory->toArray(),
                    [
                        "product_name" => $reportAgencyInventory->product?->name
                    ]
                );
            }
        }

        $agencies->getCollection()->transform(function ($agencyInventory) use ($agencyInventories) {
            $divisions = $agencyInventory->localies->pluck('name')->toArray();

            $agencyInventory->localiesName = implode(', ', $divisions);
            $agencyInventory->products     = $agencyInventories[$agencyInventory->id] ?? [];

            return $agencyInventory;
        });

        $agencies->setCollection($this->makeNewCollectionInventory($agencies->getCollection()));

        return new TableHelper(
            collections: $agencies,
            nameTable: 'agency-inventory-list',
        );
    }

    protected function makeNewCollectionInventory($inventories)
    {
        $result          = collect();
        $currentDate     = Carbon::now();
        $statusEnough    = "<span class='badge badge-light-success rounded-3 m-auto' style='padding: 5px 10px'>Tốt</span>";
        $statusNotEnough = "<span class='badge badge-light-danger rounded-3 m-auto' style='padding: 5px 10px'>Thiếu</span>";

        foreach ($inventories as $inventory) {
            $products = $inventory->products ?? [];
            usort($products, function ($a, $b) {
                return ($a["product_name"] < $b["product_name"]) ? -1 : 1;
            });
            $products   = array_values(array_filter($products, function ($product) {
                $startNum     = $product['start_num'] ?? 0;
                $importNum    = $product['import_num'] ?? 0;
                $exportNum    = $product['export_num'] ?? 0;
                $inventoryNum = $product['inventory_num'] ?? 0;

                return $startNum || $importNum || $exportNum || $inventoryNum;
            }, ARRAY_FILTER_USE_BOTH));
            $productQty = count($products);

            if ($productQty) {
                $firstProduct         = $products[0];
                $inventoryMonth       = $firstProduct['month'];
                $inventoryYear        = $firstProduct['year'];
                $inventoryAgencyId    = $firstProduct['agency_id'];
                $inventoryProductId   = $firstProduct['product_id'];
                $canRegisterInventory = !(
                    ($inventoryMonth == $currentDate->month && $inventoryYear == $currentDate->year)
                    || $inventoryYear > $currentDate->year
                );

                $buttonSaveInventory = "<button class='btn btn-success btn-save-inventory rounded-3 m-auto'
                    data-month='$inventoryMonth'
                    data-year='$inventoryYear'
                    data-agency='$inventoryAgencyId'
                    data-product='$inventoryProductId'
                    style='padding: 5px 10px; cursor: pointer'>
                    Kết chuyển
                </button>";

                $result->push((object)[
                    'agency_info'   => [
                        "value"     => "<b>Tên: </b> $inventory->name <br> <b>Địa bàn: </b> $inventory->localiesName
                        <br> <b>Mã: </b> $inventory->code",
                        "attribute" => "rowspan='$productQty'"
                    ],
                    'product_name'  => $firstProduct['product_name'],
                    'start_num'     => Helper::formatPrice($firstProduct['start_num'] ?? 0),
                    'import_num'    => Helper::formatPrice($firstProduct['import_num'] ?? 0),
                    'export_num'    => Helper::formatPrice($firstProduct['export_num'] ?? 0),
                    'inventory_num' => Helper::formatPrice($firstProduct['inventory_num'] ?? 0),
                    'status'        => $firstProduct['inventory_num'] < 0 ? $statusNotEnough : $statusEnough,
                    'features'      => !$canRegisterInventory ? '' : (
                    $firstProduct['status'] == ReportAgencyInventory::STATUS_NOT_KC && $canRegisterInventory
                        ? ($firstProduct['inventory_num'] >= 0 ? $buttonSaveInventory : '')
                        : "<span class='badge badge-light-success rounded-3 m-auto' style='padding: 5px 10px'>Đã KC</span>"
                    )
                ]);

                unset($products[0]);

                foreach ($products as $product) {
                    $inventoryMonth       = $product['month'];
                    $inventoryYear        = $product['year'];
                    $inventoryAgencyId    = $product['agency_id'];
                    $inventoryProductId   = $product['product_id'];
                    $canRegisterInventory = !(
                        ($inventoryMonth == $currentDate->month && $inventoryYear == $currentDate->year)
                        || $inventoryYear > $currentDate->year
                    );

                    $buttonSaveInventory = "<button class='btn btn-success btn-save-inventory rounded-3 m-auto'
                        data-month='$inventoryMonth'
                        data-year='$inventoryYear'
                        data-agency='$inventoryAgencyId'
                        data-product='$inventoryProductId'
                        style='padding: 5px 10px; cursor: pointer'>
                        Kết chuyển
                    </button>";
                    $startNum            = $product['start_num'] ?? 0;
                    $importNum           = $product['import_num'] ?? 0;
                    $exportNum           = $product['export_num'] ?? 0;
                    $inventoryNum        = $product['inventory_num'] ?? 0;
                    $row                 = [
                        'agency_info'   => ['hidden' => true],
                        'product_name'  => $product['product_name'],
                        'start_num'     => Helper::formatPrice($startNum),
                        'import_num'    => Helper::formatPrice($importNum),
                        'export_num'    => Helper::formatPrice($exportNum),
                        'inventory_num' => Helper::formatPrice($inventoryNum),
                        'status'        => $product['inventory_num'] < 0 ? $statusNotEnough : $statusEnough,
                        'features'      => !$canRegisterInventory ? '' : (
                        $product['status'] == ReportAgencyInventory::STATUS_NOT_KC && $canRegisterInventory
                            ? ($product['inventory_num'] >= 0 ? $buttonSaveInventory : '')
                            : "<span class='badge badge-light-success rounded-3 m-auto' style='padding: 5px 10px'>Đã KC</span>"
                        )
                    ];

                    $result->push((object)$row);
                }
            }
        }

        return $result;
    }

    public function saveInventory($data)
    {
        try {
            $agencyId  = $data['agency_id'] ?? null;
            $productId = $data['product_id'] ?? null;
            $month     = $data['month'] ?? null;
            $year      = $data['year'] ?? null;

            $reportAgencyInventory = $this->repository->getInventory($agencyId, $productId, $month, $year);

            if (!$reportAgencyInventory) {
                return [
                    'result'  => false,
                    'message' => 'Không tìm thấy dữ liệu kết chuyển',
                ];
            }

            $reportAgencyInventory->status = ReportAgencyInventory::STATUS_KC;
            $reportAgencyInventory->save();

            return [
                'result'  => true,
                'message' => 'Kết chuyển thành công',
            ];
        } catch (\Exception $exception) {
            Log::error(__METHOD__ . ' error: ' . $exception->getMessage());
            Log::error($exception);

            return [
                'result'  => false,
                'message' => 'Kết chuyển thất bại'
            ];
        }
    }

    public function export($hash_id, $requestParams, $showOption, $fileDir = 'agency_inventory')
    {
        $currentDate            = Carbon::now();
        $requestParams['month'] = $requestParams['month'] ?? $currentDate->month;
        $requestParams['year']  = $requestParams['year'] ?? $currentDate->year;

        $productIds = null;
        if (isset($requestParams['product_type']) || isset($requestParams['codeOrNameProduct'])) {
            $productIds = $this->productRepository->getForSearchInventory(
                $requestParams['product_type'] ?? null,
                $requestParams['codeOrNameProduct'] ?? null
            )->pluck('id')->toArray();
        }
        $requestParams['product_ids'] = $productIds;

        $agencies = $this->agencyRepository->queryForInventoryHistory(
            $requestParams,
            []
        )->get();

        $requestParams['agency_ids'] = $agencies->pluck('id')->toArray();

        $query = $this->repository->getQueryExportListScreen(
            ['agency.division', 'product'],
            $requestParams,
            $showOption
        );

        $file_name   = 'agency_inventory' . '_' . Carbon::now()->timestamp . "_.csv";
        $export_data = request()->get('export_agency_inventory_data', cache()->get($hash_id)) ?: [];

        $export_options = [
            'hash_id'   => $hash_id,
            'file_name' => $file_name,
            'file_dir'  => $fileDir,
            'headers'   => [
                "Đại lý",
                "Địa bàn",
                "Sản phẩm",
                "Tồn đầu",
                "Nhập",
                "Xuất",
                "Tồn (Lý thuyết)",
            ],
            'limit'     => 500,
        ];

        if (!$export_data) {
            $export_options['total'] = $query->getQuery()->getCountForPagination();
        }

        $exportService = new ExportService($export_data, $query, $export_options);

        $export_data      = $exportService->exportProgress(function ($agencyInventory, $key) {
            $divisions = $agencyInventory->localies?->pluck('name')->toArray();

            $result = [
                $agencyInventory->agency?->name,
                is_array($divisions) ? implode(', ', $divisions) : '',
                $agencyInventory->product?->name,
                $agencyInventory->start_num,
                $agencyInventory->import_num,
                $agencyInventory->export_num,
                $agencyInventory->inventory_num,
            ];

            return $result;
        });

        return response()->json($export_data);
    }

    public function updateInventory($agencyId, $productId, $year, $month, $export, $import)
    {
        $currentDate = Carbon::now();
        $agency = $this->agencyRepository->find($agencyId);
        $product = $this->productRepository->find($productId);

        $agencyInventory = $this->repository->getInventory($agencyId, $productId, $month, $year);

        if (isset($agencyInventory)) {
            if ($agencyInventory->status == ReportAgencyInventory::STATUS_NOT_KC) {
                $agencyInventory->import_num    += $import;
                $agencyInventory->export_num    += $export;
                $agencyInventory->inventory_num = $agencyInventory->start_num + $agencyInventory->import_num - $agencyInventory->export_num;
                $agencyInventory->save();
            } else {
                return [
                    'result' => false,
                    'message' => "Dữ liệu của sản phẩm $product->name thuộc đại lý $agency->name đã được kết chuyển"
                ];
            }
        } else {
            $this->repository->create([
                'year'          => $year,
                'month'         => $month,
                'agency_id'     => $agencyId,
                'product_id'    => $productId,
                'start_num'     => 0,
                'import_num'    => $import,
                'export_num'    => $export,
                'inventory_num' => $import - $export,
                'status'        => ReportAgencyInventory::STATUS_NOT_KC
            ]);
        }

        if ($year < $currentDate->year || ($year == $currentDate->year && $month < $currentDate->month)) {
            for ($i = $year; $i <= $currentDate->year; $i++) {
                $limitMonth = $i == $currentDate->year ? $currentDate->month : 12;
                $startMonth = $i == $year ? $month + 1 : 1;

                for ($j = $startMonth; $j <= $limitMonth; $j++) {
                    $agencyInventoryInMonth = $this->repository->getInventory($agencyId, $productId, $j, $i);
                    $carbon = Carbon::create($i, $j)->subMonths(1);
                    $agoMonthAgencyInventory = $this->repository->getInventory($agencyId, $productId, $carbon->month, $carbon->year);

                    if (isset($agencyInventoryInMonth)) {
                        if ($agoMonthAgencyInventory->status == ReportAgencyInventory::STATUS_NOT_KC) {
                            $agencyInventoryInMonth->start_num     = $agoMonthAgencyInventory->inventory_num;
                            $agencyInventoryInMonth->inventory_num = $agencyInventoryInMonth->start_num
                                + $agencyInventoryInMonth->import_num
                                - $agencyInventoryInMonth->export_num;
                            $agencyInventoryInMonth->save();
                        }
                    } else {
                        $this->repository->create([
                            'year'          => $i,
                            'month'         => $j,
                            'agency_id'     => $agencyId,
                            'product_id'    => $productId,
                            'start_num'     => $agoMonthAgencyInventory->inventory_num,
                            'import_num'    => 0,
                            'export_num'    => 0,
                            'inventory_num' => $agoMonthAgencyInventory->inventory_num,
                            'status'        => ReportAgencyInventory::STATUS_NOT_KC
                        ]);
                    }
                }
            }
        }

        return [
            'result' => true,
            'message' => "Dữ liệu được cập nhập thành công"
        ];
    }
}
