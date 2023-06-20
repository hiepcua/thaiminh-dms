<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Helpers\SearchFormHelper;
use App\Helpers\TableHelper;
use App\Models\Organization;
use App\Models\ProductGroup;
use App\Models\RevenuePeriod;
use App\Models\Store;
use App\Models\StoreRank;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ProductGroup\ProductGroupRepositoryInterface;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Repositories\StoreRank\StoreRankRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ReportRevenueStoreRankService extends BaseService
{
    public function __construct(
        protected StoreRankRepositoryInterface    $repository,
        protected ProductRepositoryInterface      $productRepository,
        protected ProductGroupRepositoryInterface $productGroupRepository,
        protected OrganizationRepositoryInterface $organizationRepository,
        protected StoreRepositoryInterface        $storeRepository,
    )
    {
        parent::__construct();
    }

    function setModel()
    {
        return new StoreRank();
    }

    public function mapStoreTypeProductType()
    {
        return [
            RevenuePeriod::STORE_TYPE_RETAIL      => [
                ProductGroup::PRODUCT_TYPE_QC, ProductGroup::PRODUCT_TYPE_TV
            ],
            RevenuePeriod::STORE_TYPE_CHAIN_STORE => [
                ProductGroup::PRODUCT_TYPE_CHAIN_STORE
            ],
            RevenuePeriod::STORE_TYPE_MARKET      => [
                ProductGroup::PRODUCT_TYPE_MARKET, ProductGroup::PRODUCT_TYPE_MARKET_QC,
            ],
        ];
    }

    public function indexOptions($hasExport = true): array
    {
        $searchOptions           = [];
        $searchOptions[]         = [
            'type'         => 'text',
            'name'         => 'search[name]',
            'placeholder'  => 'Mã/Tên nhà thuốc',
            'defaultValue' => request('search.name'),
        ];
        $storeType               = request('search.store_type', RevenuePeriod:: STORE_TYPE_RETAIL);
        $searchOptions[]         = [
            'type'         => 'selection',
            'name'         => 'search[store_type]',
            'id'           => 'select_store_type',
            'defaultValue' => $storeType,
            'options'      => ['' => '- Loại NT -'] + RevenuePeriod::STORE_TYPE_TEXTS
        ];
        $productType             = request('search.product_type', ProductGroup::PRODUCT_TYPE_QC);
        $mapStoreTypeProductType = $this->mapStoreTypeProductType();
        $searchOptions[]         = [
            'type'         => 'selection',
            'name'         => 'search[product_type]',
            'id'           => 'select_product_type',
            'attributes'   => 'selected_product_type="' . $productType . '"',
            'defaultValue' => $productType,
            'options'      => ['' => '- Loại hàng -'] + collect(ProductGroup::PRODUCT_TYPES)->map(function ($item, $key) use ($storeType, $mapStoreTypeProductType) {
                    //
                    $display = in_array($key, $mapStoreTypeProductType[$storeType]);
                    return [
                        'name'       => 'Loại ' . $item['text'],
                        'attributes' => sprintf('data-period_of_year="%s" style="%s"', $item['period_of_year'], $display ? '' : 'display:none;'),
                    ];
                })->toArray()
        ];
        $periodOptions           = collect(ProductGroup::PRODUCT_TYPES)
            ->pluck('period_of_year')
            ->unique()
            ->map(function ($periodOfYear) use ($productType) {
                $options = Helper::periodOptions(null, $periodOfYear);

                return collect($options)->map(function ($item) use ($periodOfYear, $productType) {
                    $defaultPeriodOfYear = ProductGroup::PRODUCT_TYPES[$productType]['period_of_year'] ?? 0;

                    $item['key']    = $item['started_at'] . '_' . $item['ended_at'];
                    $item['values'] = [
                        'name'       => $item['name'],
                        'attributes' => sprintf('data-period_of_year="%s" style="%s"', $periodOfYear, $defaultPeriodOfYear == $periodOfYear ? '' : 'display:none;'),
                    ];

                    return $item;
                })->pluck('values', 'key')->toArray();
            })->mapWithKeys(function ($item) {
                return $item;
            })->toArray();
        $searchOptions[]         = [
            'type'         => 'selection',
            'name'         => 'search[period]',
            'id'           => 'select_period',
            'defaultValue' => request('search.period'),
            'options'      => ['' => '- Chu kỳ -'] + $periodOptions
        ];
        $searchDivisionId        = request('search.division_id');
        $localityId              = request('search.locality_id');
        $searchOptions[]         = [
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
                    'multiple'   => true,
                    'name'       => 'search[division_id][]',
                    'class'      => '',
                    'id'         => 'division_id',
                    'attributes' => '',
                    'selected'   => $searchDivisionId
                ]
            ],
        ];
        $localityOptions         = $searchDivisionId ? $this->organizationRepository->getLocalityByDivision($searchDivisionId)->pluck('name', 'id')->toArray() : [];
        $searchOptions[]         = [
            'type'          => 'selection',
            'name'          => 'search[locality_id]',
            'defaultValue'  => $localityId,
            'id'            => 'form-locality_id',
            'options'       => ['' => '- Địa bàn -'] + $localityOptions,
            'other_options' => ['option_class' => 'ajax-locality-option'],
        ];
        $searchOptions[]         = [
            'type'         => 'hidden',
            'name'         => 'search[hash_id]',
            'defaultValue' => request('search.hash_id', md5('513_' . mt_rand() . time())),
        ];

        $permissionExport = (request('search') && $hasExport) ? 'download_bao_cao_thuong_key_qc' : '';

        $searchForm = SearchFormHelper::getForm(
            route: route('admin.report.revenue.store.key_qc'),
            method: 'GET',
            config: $searchOptions,
            routeExport: 'admin.report.revenue.store.key_qc.export',
            permissionExport: $permissionExport
        );

        return compact('searchForm', 'mapStoreTypeProductType');
    }

    function getGroups(): \Illuminate\Database\Eloquent\Collection|array
    {
        static $cache;
        $productType = request('search.product_type') ?: ProductGroup::PRODUCT_TYPE_QC;
        if (empty($cache)) {
            $cache = $this->productGroupRepository->getGroupByType($productType);
        }
        return $cache->where('parent_id', 0)
            ->map(function ($item) use ($cache) {
                $item->children = $cache->where('parent_id', $item->id);
                return $item;
            });
    }

    function summaryRevenue(array $searchParams): TableHelper
    {
        ini_set('memory_limit', '256M');
        $rows = collect([]);
        $this->repository->getStoreSummary($searchParams)
            ->groupBy('store_parent_id')
            ->each(function ($items, $key) use (&$rows) {
                $totalRev  = $items->sum(function ($item) {
                    return $item->bonus + $item->bonus_product_priority;
                });
                $totalItem = $items->count();
                $items     = $items->map(function ($item) {
                    $item->sort_parent = $item->store_parent_id == $item->store_id ? 0 : 1;
                    return $item;
                })
                    ->sortBy('sort_parent')
                    ->map(function ($item) use ($totalItem) {
                        $item->col_attr = '';
                        if ($totalItem > 1) {
                            if ($item->sort_parent === 0) {
                                $item->col_attr = 'style="--bs-table-accent-bg: orange;color: #000;"';
                            } else {
                                $item->col_attr = 'style="--bs-table-accent-bg: #ffdd9f;color: #000;"';
                            }
                        }

                        return $item;
                    });
//                if ($key == 28540) {
//                    dd($items->toArray());
//                }

                foreach ($items as $item) {
                    $rev = $item->revenue;

                    $row = (object)[
                        'col_dia_ban2'          => ['value' => $item->id, 'attribute' => $item->col_attr],
                        'col_dia_ban'           => ['value' => $item->store?->organization?->name, 'attribute' => $item->col_attr],
                        'col_tdv'               => ['value' => $item->store?->organization?->users?->pluck('username')->join(', '), 'attribute' => $item->col_attr],
                        'col_asm'               => ['value' => $item->store?->organization?->parent?->users?->pluck('username')->join(', '), 'attribute' => $item->col_attr],
                        'col_store_code'        => ['value' => $item->store?->code ?? '', 'attribute' => $item->col_attr],
                        'col_parent_store_code' => ['value' => $item->store?->store_parent?->code ?? '', 'attribute' => $item->col_attr],
                        'col_store_name'        => ['value' => $item->store?->name, 'attribute' => $item->col_attr],
                        'col_rev_total'         => ['value' => Helper::formatPrice($rev), 'attribute' => $item->col_attr],
                        'col_bonus_rev'         => ['value' => Helper::formatPrice($item->bonus), 'attribute' => $item->col_attr],
                        'col_bonus_priority'    => ['value' => Helper::formatPrice($item->bonus_product_priority), 'attribute' => $item->col_attr],
                        'col_bonus_total'       => ['value' => Helper::formatPrice($item->bonus + $item->bonus_product_priority), 'attribute' => $item->col_attr],
                        '_store_id'             => $item->store_id,
                        '_rev_total'            => $rev,
                        '_bonus_rev'            => $item->bonus,
                        '_bonus_priority'       => $item->bonus_product_priority,
                        '_bonus_total'          => $item->bonus + $item->bonus_product_priority,
                        'sort_rev'              => $totalRev,
                    ];
                    $rows->add($row);
                }
            });
        $rows = $rows->sortByDesc('sort_rev');

        if ($searchParams['hash_id']) {
            Storage::put(sprintf('%s/%s.json', StoreRank::FOLDER_CACHE_JSON_513, $searchParams['hash_id']), $rows->pluck('_store_id')->toJson());
            cache()->forget($searchParams['hash_id']);
            cache()->forget($searchParams['hash_id'] . '_other');
        }

        $takeRows = $rows->count() > StoreRank::LIMIT_ROW_513 ? $rows->take(StoreRank::LIMIT_ROW_513) : $rows;

        if ($takeRows->isNotEmpty()) {
            $rowTotal = (object)[
                'col_dia_ban2'          => '',
                'col_dia_ban'           => '',
                'col_tdv'               => '',
                'col_asm'               => '',
                'col_store_code'        => '',
                'col_parent_store_code' => '',
                'col_store_name'        => '',
                'col_rev_total'         => Helper::formatPrice($takeRows->sum('_rev_total')),
                'col_bonus_rev'         => Helper::formatPrice($takeRows->sum('_bonus_rev')),
                'col_bonus_priority'    => Helper::formatPrice($takeRows->sum('_bonus_priority')),
                'col_bonus_total'       => Helper::formatPrice($takeRows->sum('_bonus_total')),
            ];
            $takeRows->add($rowTotal);
        }

        $headers     = [
            'col_dia_ban'           => '',
            'col_tdv'               => '',
            'col_asm'               => '',
            'col_store_code'        => '',
            'col_parent_store_code' => '',
            'col_store_name'        => '',
            'col_rev_total'         => '',
            'col_bonus_rev'         => '',
            'col_bonus_priority'    => '',
            'col_bonus_total'       => '',
        ];
        $classCustom = [
            'col_rev_total'      => 'text-end',
            'col_bonus_rev'      => 'text-end',
            'col_bonus_priority' => 'text-end',
            'col_bonus_total'    => 'text-end fw-bolder',
        ];

        return new TableHelper(
            collections: $takeRows,
            nameTable: 'report-store-key-qc',
            headers: $headers,
            classCustom: $classCustom,
            headerHtml: $this->tableThead(),
            isPagination: false,
            totalRow: $takeRows->count() - 1,
        );
    }

    function tableThead()
    {
        // HEADERS
        $headers                                = [];
        $headers[1]                             = [
            ['value' => 'Thông tin tổng quan', 'attributes' => ['colspan' => 1, 'class' => 'text-center']]
        ];
        $headers[2]                             = [
            ['value' => 'Địa bàn', 'attributes' => ['class' => 'text-center']],
            ['value' => 'Tên TDV', 'attributes' => ['class' => 'text-center']],
            ['value' => 'Tên Asm', 'attributes' => ['class' => 'text-center']],
            ['value' => 'Mã nhà thuốc', 'attributes' => ['class' => 'text-center']],
            ['value' => 'Mã nhà thuốc cha', 'attributes' => ['class' => 'text-center']],
            ['value' => 'Tên nhà thuốc', 'attributes' => ['class' => 'text-center']],
            ['value' => 'Tổng Doanh thu', 'attributes' => ['class' => 'text-center']],
            ['value' => 'Thưởng doanh thu', 'attributes' => ['class' => 'text-center']],
            ['value' => 'Thưởng SP ưu tiên', 'attributes' => ['class' => 'text-center']],
            ['value' => 'Tổng thưởng', 'attributes' => ['class' => 'text-center']],
        ];
        $headers[1][0]['attributes']['colspan'] = count($headers[2]);

        return view('pages.reports.revenue_store.key_qc.table_thead', compact('headers'));
    }

    function exportOptions(array $searchParams)
    {
        $filename = sprintf('DOANH_THU_KHACH_HANG_KEY_%s_%s_%s.xlsx', $searchParams['from_date'], $searchParams['to_date'], now()->getTimestamp());
        return [
            'hash_id'      => request('hash_id', ''),
            'file_name'    => $filename,
            'file_dir'     => 'download_bao_cao_thuong_key_qc',
            'multi_header' => true,
            'merge_header' => true,
            'limit'        => 500,
        ];
    }

    function exportOtherValues(array $searchParams): array
    {
        $searchParams['store_ids'] = [];

        //group SPUT
        $query                      = $this->repository->queryKey($searchParams, true);
        $query->getQuery()->orders  = null;
        $query->getQuery()->groups  = null;
        $query->getQuery()->columns = null;
        $query->where('store_rank_items.is_product_priority', 1);
        $query->selectRaw('DISTINCT store_rank_items.group_id')
            ->orderBy('store_rank_items.group_id');
        $groupPriority = $query->get()->pluck('group_id')->toArray();

        // Thong tin
        $query                     = $this->repository->queryKey($searchParams, true);
        $query->getQuery()->orders = null;
        $query->getQuery()->groups = null;
        $query->select(['store_rank_items.group_id', 'store_rank_items.product_id', 'store_rank_items.sub_group_id'])
            ->groupBy(['store_rank_items.product_id', 'store_rank_items.sub_group_id']);

        $results     = $query->get();
        $groupValues = $groupIds = $productIds = $subGroupIds = $groupTree = [];
        foreach ($results as $item) {
            $groupValues[$item->group_id][$item->product_id] = $item->product_id;
            $groupTree[$item->group_id][$item->sub_group_id] = $item->sub_group_id;
            $groupIds[$item->group_id]                       = $item->group_id;
            $subGroupIds[$item->sub_group_id]                = $item->sub_group_id;
            $productIds[$item->product_id]                   = $item->product_id;
        }
        $productGroups = $this->productGroupRepository->getByArrId(array_merge($groupIds, $subGroupIds, $groupPriority));
        $products      = $this->productRepository->getByArrId($productIds);
        foreach ($groupTree as $gid => $subIds) {
            sort($groupTree[$gid]);
        }

        return compact('groupValues', 'groupTree', 'productGroups', 'groupPriority', 'products');
    }

    /**
     * @param $hashId
     * @param array $searchParams
     * @return JsonResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws ReaderNotOpenedException
     * @throws WriterNotOpenedException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    function exportRevenue($hashId, array $searchParams): \Illuminate\Http\JsonResponse
    {
        // LAY ID DA DUOC SAP XEP TU KET QUA TIM KIEM
        $file = Storage::get(sprintf('%s/%s.json', StoreRank::FOLDER_CACHE_JSON_513, $hashId));
        $json = collect(json_decode($file, true) ?: []);
        // EXPORT DATA
        $exportData    = request('export_key_qc_data', cache()->get($hashId)) ?: [];
        $exportOptions = $this->exportOptions($searchParams);
        // SEARCH STORE ID
        $sliceOffset               = (($exportData['current_step'] ?? 1) - 1) * $exportOptions['limit'];
        $searchParams['store_ids'] = $json->slice($sliceOffset, $exportOptions['limit'])->toArray();
//        $searchParams['store_ids'] = [28480];
        $queryStringOrderField = collect(array_fill(0, count($searchParams['store_ids']), '?'))->join(', ');

        if (!$exportData) {
            $exportOptions['total'] = $json->count();
        } else {
            $exportData['offset'] = 0;
        }
//        cache()->forget($hashId . '_other');
        $otherOptions                 = cache()->remember($hashId . '_other', now()->addHour(), function () use ($searchParams) {
            list(
                'groupValues' => $groupValues,
                'groupTree' => $groupTree,
                'productGroups' => $productGroups,
                'groupPriority' => $groupPriority,
                'products' => $products,
                ) = $this->exportOtherValues($searchParams);

            $mergeCells = [];
            $headers    = [];
            $headers[1] = [
                ['value' => 'Thông tin nhà thuốc', 'rowspan' => 1, 'colspan' => 9],
                '', '', '', '', '', '', '', '', '',
            ];
            $headers[2] = ['', '', '', '', '', '', '', '', '', '',];
            $headers[3] = ['Miền', 'Tỉnh / Thành Phố', 'Địa bàn', 'Tên TDV', 'Tên Asm', 'Mã nhà thuốc', 'Mã nhà thuốc cha', 'Tên nhà thuốc', 'Địa chỉ', 'Điện thoại',];

            $rowColumns = [
                'col_region', 'col_province', 'col_locality', 'col_tdv', 'col_asm',
                'col_code', 'col_parent_code', 'col_name', 'col_address', 'col_phone',
            ];
            //Số lượng sản phẩm
            $countColumnProduct = collect($groupValues)->sum(function ($items) {
                return count($items) + 1;
            });
            $headers[1][]       = [
                'value'   => 'Số lượng sản phẩm',
                'colspan' => $countColumnProduct - 1
            ];
            $headers[1]         = array_merge($headers[1], array_fill(0, $countColumnProduct - 1, ''));
            foreach ($groupValues as $gid => $pIds) {
                $headers[2][] = ['value' => $productGroups->where('id', $gid)->first()->name, 'colspan' => count($pIds)];
                $headers[2]   = array_merge($headers[2], array_fill(0, count($pIds), ''));
                foreach ($pIds as $pId) {
                    $headers[3][] = $products->where('id', $pId)->first()->name;
                    //
                    $rowColumns[] = "col_qty_$pId";
                }
                $headers[3][] = 'Số lượng SPƯT';
                $rowColumns[] = "col_priority_qty_$gid";
            }
            //Doanh thu
            $countColumnRevenue = collect($groupTree)->flatten()->count() + count($groupTree);
            $headers[1][]       = [
                'value'   => 'Doanh thu',
                'colspan' => $countColumnRevenue
            ];
            $headers[1]         = array_merge($headers[1], array_fill(0, $countColumnRevenue, ''));
            foreach ($groupTree as $gid => $subIds) {
                $headers[2][] = [
                    'value'   => $productGroups->where('id', $gid)->first()->name,
                    'colspan' => count($subIds),
                ];
                $headers[2]   = array_merge($headers[2], array_fill(0, count($subIds), ''));
            }
            foreach ($groupTree as $gid => $subIds) {
                foreach ($subIds as $subId) {
                    $headers[3][] = ['value' => $productGroups->where('id', $subId)->first()->name,];
                    $rowColumns[] = "col_rev_{$gid}_$subId";
                }
                $headers[3][] = ['value' => 'Hạng',];
                $rowColumns[] = "col_rank_$gid";
            }
            $headers[2][] = ['value' => 'Tổng Doanh thu'];
            $headers[3][] = '';
            $rowColumns[] = "col_rev_total";
            //Thưởng
            $headers[1][]     = [
                'value'   => 'Thưởng',
                'colspan' => (count($groupValues) * 3)
            ];
            $countColumnBonus = count($groupValues) * 3;
            $headers[1]       = array_merge($headers[1], array_fill(0, $countColumnBonus, ''));
            foreach (array_keys($groupValues) as $gid) {
                $headers[2][] = [
                    'value'   => $productGroups->where('id', $gid)->first()->name,
                    'colspan' => 2,
                ];
                $headers[2][] = '';
                $headers[2][] = '';
                $headers[3][] = ['value' => 'Hạng'];
                $headers[3][] = ['value' => 'SPƯT'];
                $headers[3][] = ['value' => 'Tổng thưởng'];
                //
                $rowColumns[] = "col_bonus_$gid";
                $rowColumns[] = "col_bonus_priority_$gid";
                $rowColumns[] = "col_total_bonus_$gid";
            }
            $headers[2][] = ['value' => 'Tổng thưởng', 'rowspan2' => 1];
            $headers[3][] = '';
            $rowColumns[] = "col_total_bonus";
            //

            foreach ($headers as $i => $header) {
                foreach ($header as $j => $item) {
                    if (is_array($item)) {
                        if (isset($item['colspan']) && isset($item['rowspan'])) {
                            $mergeCells[] = [$j, $i, $j + $item['colspan'], $i + $item['rowspan']];
                        } elseif (isset($item['colspan'])) {
                            $mergeCells[] = [$j, $i, $j + $item['colspan'], $i];
                        } elseif (isset($item['rowspan'])) {
                            $mergeCells[] = [$j, $i, $j, $item['rowspan']];
                        }
                    }
                }
            }

            return [
                'headers'     => $headers,
                'row_columns' => $rowColumns,
                'merge_cells' => $mergeCells,
                'group_ids'   => array_keys($groupValues),
                'group_tree'  => $groupTree,
            ];
        });
        $exportOptions['headers']     = $otherOptions['headers'];
        $exportOptions['row_columns'] = $otherOptions['row_columns'];
        $exportOptions['merge_cells'] = $otherOptions['merge_cells'];
        $searchParams['_group_ids']   = $otherOptions['group_ids'];
        $searchParams['_group_tree']  = $otherOptions['group_tree'];

        // QUERY
        $query                     = $this->repository->queryStoreDetail($searchParams);
        $query->getQuery()->orders = null;
        $query->orderByRaw("FIELD(store_id, $queryStringOrderField)", $searchParams['store_ids']);
//        dd(Helper::getSql($query));

        // EXPORT
        $exportService = new ExportService($exportData, $query, $exportOptions);
        $output        = $exportService->exportProgress(function ($row, $key) use ($exportOptions) {
            $rows = [];
            foreach ($exportOptions['row_columns'] as $_key) {
                if ($_key) {
                    $rows[] = $row->{$_key} ?? '';
                } else {
                    $rows[] = '';
                }
            }
            return $rows;
        }, function ($results) use ($searchParams) {
            // prepare rows
            return $this->parseResultExport($results, $searchParams);
        });

        return response()->json($output);
    }

    function parseResultExport($results, $searchParams)
    {
        $details = $this->repository->detailKeyQc($searchParams);
        $results->map(function ($item) use ($details, $searchParams) {
            $item->item_details = ($details[$item->store_id] ?? []);
            $item->group_ids    = $searchParams['_group_ids'] ?? [];
            $item->group_tree   = $searchParams['_group_tree'] ?? [];

            return $this->parseRowDetail($item);
        });

        return $results;
    }

    function parseRowDetail($item)
    {

        $item->col_region      = $item->store?->province?->region ?? '-';
        $item->col_province    = $item->store?->province?->province_name_with_type ?? '-';
        $item->col_locality    = $item->store?->organization?->name ?? '-';
        $item->col_tdv         = $item->store?->organization?->users?->pluck('username')->join(', ') ?? '-';
        $item->col_asm         = $item->store?->organization?->parent?->users?->pluck('username')->join(', ') ?? '-';
        $item->col_code        = $item->store?->code ?? '-';
        $item->col_parent_code = $item->store?->store_parent?->code ?? '-';
        $item->col_name        = $item->store?->name ?? '-';
        $item->col_address     = $item->store?->full_address ?? '-';
        $item->col_phone       = $item->store?->phone_owner ?? $item->store?->phone_web ?? '-';
        foreach ($item->item_details['quantities'] ?? [] as $pid => $qty) {
            $item->{"col_qty_$pid"} = $qty;
        }
        $item->col_rev_total      = $item->revenue_total ?? 0;
        $item->col_total_bonus    = 0;
        $item->col_bonus_priority = $item->bonus_product_priority ?? 0;
        foreach ($item->group_ids as $gid) {
            $item->{"col_rev_$gid"}            = $item->{"revenue_$gid"} ?? '';
            $item->{"col_rank_$gid"}           = $item->{"rank_$gid"} ?? '';
            $item->{"col_bonus_$gid"}          = $item->{"bonus_$gid"} ?? '';
            $item->{"col_bonus_priority_$gid"} = $item->{"bonus_product_priority_$gid"} ?? 0;
            $item->{"col_total_bonus_$gid"}    = ($item->{"bonus_product_priority_$gid"} ?? 0) + ($item->{"bonus_$gid"} ?? 0);

            $item->col_total_bonus += ($item->{"col_bonus_priority_$gid"} ?? 0);
            $item->col_total_bonus += ($item->{"bonus_$gid"} ?? 0);
        }
        foreach ($item->group_tree as $gid => $subIds) {
            foreach ($subIds as $subId) {
                $item->{"col_rev_{$gid}_$subId"} = $item->{"revenue_{$gid}_$subId"} ?? '';
            }
        }

        foreach ($item->item_details['priorities'] ?? [] as $gid => $priority) {
            $item->{"col_priority_qty_$gid"} = $priority;
        }

        return $item;
    }

    function parseSearchParams($requestParams): array
    {
        list($fromDate, $toDate) = explode('_', ($requestParams['search']['period'] ?? '_'));
        $groups = $this->getGroups();
        if (empty($requestParams['search']['group'])) {
            $requestParams['search']['group'] = $groups->pluck('id')->toArray();
        } else {
            $requestParams['search']['group'] = (array)$requestParams['search']['group'];
        }
        $subGroups = [];
        foreach ($groups as $item) {
            if (in_array($item->id, $requestParams['search']['group'])) {
                $subGroups[$item->id] = $item->children->pluck('id')->toArray();
            }
        }
        $localityIds = $requestParams['search']['locality_id'] ?? [];
        $divisionId  = $requestParams['search']['division_id'] ?? 0;
        if ($divisionId && !$localityIds) {
            $localityIds = $this->organizationRepository->getLocalityByDivision($divisionId)->pluck('id')->toArray();
        }
        $storeIds = [];
        if ($requestParams['search']['name'] ?? '') {
            $storeIds    = $this->storeRepository->getQueryByRequest([], [
                'name'   => $requestParams['search']['name'] ?? '',
                'status' => Store::STATUS_ACTIVE_TEXT
            ])->select('id')->get()->pluck('id')->toArray();
            $localityIds = [];
        }

        return [
            'store_type'   => $requestParams['search']['store_type'] ?? RevenuePeriod:: STORE_TYPE_RETAIL,
            'product_type' => $requestParams['search']['product_type'] ?? ProductGroup::PRODUCT_TYPE_QC,
            'store_ids'    => $storeIds,
            'from_date'    => $fromDate,
            'to_date'      => $toDate,
            'locality_id'  => $localityIds,
            'group'        => $requestParams['search']['group'],
            'sub_groups'   => $subGroups,
            'options'      => $requestParams['options'] ?? [],
            'hash_id'      => $requestParams['search']['hash_id'] ?? '',
        ];
    }

    public function getRevenueTypeFromStoreType($storeType)
    {
        $storeTypeRevenueType = [
            Store::STORE_TYPE_LE    => RevenuePeriod::STORE_TYPE_RETAIL,
            Store::STORE_TYPE_CHO   => RevenuePeriod::STORE_TYPE_MARKET,
            Store::STORE_TYPE_CHUOI => RevenuePeriod::STORE_TYPE_CHAIN_STORE,
        ];
        return $storeTypeRevenueType[$storeType];
    }

    public function getProductTypesFromStoreType($storeType): array
    {
        $storeTypeProductType = $this->mapStoreTypeProductType();
        $revenueType          = $this->getRevenueTypeFromStoreType($storeType);

        return $storeTypeProductType[$revenueType];
    }

    function getCurrentBonusOfStore($storeId, $storeType, $productType): array
    {
        $revenueType               = $this->getRevenueTypeFromStoreType($storeType);
        $searchParams              = [
            'store_ids'    => [$storeId],
            'store_type'   => $revenueType,
            'locality_id'  => null,
            'product_type' => $productType,
            '_group_ids'   => [],
        ];
        $periodOfYear              = ProductGroup::PRODUCT_TYPES[$productType]['period_of_year'];
        $periodOption              = collect(Helper::periodOptions(null, $periodOfYear))
            ->filter(function ($item) {
                return $item['ended_at_timestamp'] < now()->getTimestamp();
            })
            ->sortByDesc('period')
            ->first();

        if (!$periodOption) return [];

        $searchParams['from_date'] = $periodOption['started_at'];
        $searchParams['to_date']   = $periodOption['ended_at'];

        $productGroup               = $this->repository->queryKey($searchParams, true)
            ->select(['store_rank_items.group_id'])
            ->groupBy(['store_rank_items.group_id'])
            ->get();
        $searchParams['_group_ids'] = $productGroup->pluck('group_id')->toArray();
        $productGroups              = $this->productGroupRepository->getByArrId($searchParams['_group_ids']);

        $rev = $this->repository->queryStoreDetail($searchParams)->without(['store'])
            ->get()
            ->map(function ($item) use ($searchParams, $productGroups, $productType) {
                $totalBonus = $item['bonus_product_priority'] ?? 0;
                $itemInfo   = [];
                $rank       = '';
                foreach ($searchParams['_group_ids'] as $id) {
                    $_bonus       = $item["bonus_$id"] ?? 0;
                    $totalBonus   += $_bonus;
                    $productGroup = $productGroups->where('id', $id)->first();
                    if ($productGroup && $_bonus) {
                        $itemInfo[$id] = [
                            'product_type' => ProductGroup::PRODUCT_TYPES[$productType]['text'] ?? '-',
                            'product_name' => $productGroup->name,
                            'bonus'        => $_bonus,
                            'key'          => "key-$productType-$id",
                        ];
                        $rank          = $item["rank_$id"];
                    }
                }
                $item->item_info   = $itemInfo;
                $item->rank_name   = $rank;
                $item->total_bonus = $totalBonus;
                return $item;
            })
            ->first()?->toArray();

        return $rev ?: [];
    }
}
