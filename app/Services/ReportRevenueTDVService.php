<?php

namespace App\Services;

use App\Exports\AsmRevenueDetailExport;
use App\Exports\TDVSummaryExport;
use App\Helpers\Helper;
use App\Helpers\SearchFormHelper;
use App\Models\Organization;
use App\Models\ReportRevenueOrder;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\ProductGroup\ProductGroupRepositoryInterface;
use App\Repositories\ReportRevenueOrder\ReportRevenueOrderRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ReportRevenueTDVService extends BaseService
{
    public function __construct(
        protected ReportRevenueOrderRepositoryInterface $repository,
        protected ProductGroupRepositoryInterface       $productGroupRepository,
        protected OrganizationRepositoryInterface       $organizationRepository,
    )
    {
        parent::__construct();
    }

    function setModel()
    {
        return new ReportRevenueOrder();
    }

    public function indexOptions(): array
    {
        $searchOptions    = [];
        $searchBookingAt  = request('search.booking_at', implode(' to ', Helper::defaultMonthFromToDate()));
        $searchOptions[]  = [
            'type'         => 'dateRangePicker',
            'placeholder'  => 'Ngày nhập from - to',
            'name'         => 'search[booking_at]',
            'defaultValue' => $searchBookingAt,
            'id'           => 'searchRange',
        ];
        $searchDivisionId = request('search.division_id');
        $localityId       = request('search.locality_id');
        $searchOptions[]  = [
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
        $localityOptions  = $searchDivisionId ? $this->organizationRepository->getLocalityByDivision($searchDivisionId)->pluck('name', 'id')->toArray() : [];
        $searchOptions[]  = [
            'type'          => 'selection',
            'name'          => 'search[locality_id]',
            'defaultValue'  => $localityId,
            'id'            => 'form-locality_id',
            'options'       => ['' => '- Địa bàn -'] + $localityOptions,
            'other_options' => ['option_class' => 'ajax-locality-option'],
        ];
        $option_tdv       = ['' => '- TDV -'];
        if ($localityId) {
            $option_tdv = $option_tdv + $this->organizationRepository->getUserByLocality($localityId)
                    ->pluck('name', 'id')->toArray();
        }
        $searchOptions[] = [
            'type'          => 'selection',
            'name'          => 'search[user_id]',
            'defaultValue'  => request('search.user_id'),
            'id'            => 'form-user_id',
            'options'       => $option_tdv,
            'other_options' => ['option_class' => 'ajax-tdv-option'],
        ];

        $searchForm = SearchFormHelper::getForm(
            route('admin.report.revenue.tdv'),
            'GET',
            $searchOptions,
            routeExport: 'admin.report.revenue.tdv.export',
            permissionExport: 'download_don_nhap_dai_ly'
        );

        return compact('searchForm');
    }

    public function detailOptions()
    {
        $searchOptions   = [];
        $searchBookingAt = request('search.booking_at', implode(' to ', Helper::defaultMonthFromToDate()));
        $searchOptions[] = [
            'type'         => 'dateRangePicker',
            "placeholder"  => "Ngày nhập from - to",
            "name"         => "search[booking_at]",
            "defaultValue" => $searchBookingAt,
            "id"           => "searchRange",
        ];
        return compact('searchOptions');
    }

    /**
     * @param array $requestParams
     * @param bool $format
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function summaryRevenue(array $requestParams, bool $format = true): array
    {
        $searchParams = $this->parseSearchParams($requestParams);

        list('results' => $results, 'group_ids' => $groupIds) = $this->repository->getRevenueTDVSummary($searchParams);
        $productGroups = $this->productGroupRepository->getByArrId($groupIds);
        $groups        = $productGroups->where('parent_id', 0)->map(function ($item) use ($productGroups) {
            $item->sub_groups = $productGroups->where('parent_id', $item->id);
            return $item;
        });

        // HEADERS
        $headers    = [];
        $headers[1] = [
            ['value' => 'STT', 'attributes' => ['rowspan' => 2, 'class' => 'text-center text-black']],
            ['value' => 'TDV', 'attributes' => ['rowspan' => 2, 'class' => 'text-center']],
            ['value' => 'Username', 'attributes' => ['rowspan' => 2, 'class' => 'text-center text-black']],
            ['value' => 'Địa bàn', 'attributes' => ['rowspan' => 2, 'class' => 'text-center text-black']],
        ];
        $headers[2] = [[], [], [], []];
        $rowColumns = ['col_stt', 'col_tdv', 'col_username', 'col_locality',];
        foreach ($groups as $group) {
            $countChildren = $group->sub_groups->count();
            if ($countChildren) {
                $headers[1][] = [
                    'value'      => $group->name,
                    'attributes' => ['colspan' => $countChildren, 'class' => 'text-center']
                ];
                for ($_i = 0; $_i < $countChildren - 1; $_i++) {
                    $headers[1][] = [];
                }
                foreach ($group->sub_groups as $sub_group) {
                    $headers[2][] = ['value' => $sub_group->name, 'attributes' => ['class' => 'text-center']];
                    $rowColumns[] = "col_{$group->id}_$sub_group->id";
                }
            }
        }
        $headers[1][] = [
            'value'      => 'Tổng doanh số',
            'attributes' => ['rowspan' => 2, 'class' => 'text-center text-black']
        ];
        $rowColumns[] = 'col_total';

        // ROWS
//        dd($results);
        $asmRows = [];
        $stt     = 1;
        foreach ($results as $asmId => $groupTDVItems) {
            $rows = $tdvRevenue = [];

            $rowAsm = [
                'col_stt'      => ['value' => 'ASM', 'attributes' => ['class' => 'text-center fw-bolder text-black']],
                'col_tdv'      => ['value' => '', 'link' => '', 'attributes' => ['class' => 'fw-bolder']],
                'col_username' => ['value' => '', 'attributes' => ['class' => 'fw-bolder text-black']],
                'col_locality' => ['value' => ''],
                'col_total'    => ['attributes' => ['class' => 'text-end fw-bolder text-black']],
                '_col_total'   => 0,
            ];

            foreach ($groupTDVItems as $tdvId => $groupLocalityItems) {
                foreach ($groupLocalityItems as $localityId => $groupValues) {

                    $row = [
                        'col_stt'      => ['value' => $stt, 'attributes' => ['class' => 'text-center text-black']],
                        'col_tdv'      => ['value' => '',],
                        'col_username' => ['value' => '', 'attributes' => ['class' => 'text-black']],
                        'col_locality' => ['value' => '', 'attributes' => ['class' => 'text-black']],
                        'col_total'    => ['value' => '', 'attributes' => ['class' => 'text-end fw-bolder text-black'],],
                        '_col_total'   => 0,
                    ];


                    foreach ($groupValues as $value) {
                        $groupKey       = "col_{$value->product_group_id}_$value->sub_group_id";
                        $row[$groupKey] = [
                            'value'      => $value->amount,
                            'attributes' => ['class' => 'text-end text-black']
                        ];
                        if (empty($rowAsm[$groupKey])) {
                            $rowAsm[$groupKey]    = [
                                'value'      => 0,
                                'attributes' => ['class' => 'text-end text-black']
                            ];
                            $rowAsm["_$groupKey"] = 0;
                        }
                        $rowAsm[$groupKey]['value'] += $value->amount;
                        $rowAsm["_$groupKey"]       += $value->amount;
                        $row["_$groupKey"]          = $value->amount;
                        $row['_col_total']          += $value->amount;
                        $rowAsm['_col_total']       += $value->amount;

                        if (empty($row['col_tdv']['value'])) {
                            $row['col_locality']['value'] = $value->organization?->name ?? '-';
                            $row['col_username']['value'] = $value->user?->username;
                            $row['col_tdv']['value']      = $value->user?->name;
                            $row['col_tdv']['link']       = route('admin.report.revenue.tdv.detail', ['search' => array_merge(
                                    request()->get('search', []),
                                    [
                                        'user_id'     => $value->user_id,
                                        'asm_user_id' => $value->asm_user_id
                                    ]
                                )]
                            );
                        }
                        if (empty($rowAsm['col_tdv']['value'])) {
                            $rowAsm['col_username']['value'] = $value->asm?->username;
                            $rowAsm['col_tdv']['value']      = $value->asm?->name;
                            $rowAsm['col_tdv']['link']       = route('admin.report.revenue.export.asm_detail', ['search' => array_merge(
                                request()->get('search', []),
                                [
                                    'asm_user_id' => $value->asm_user_id
                                ]
                            )]);
                        }
                    }
                    $row = $this->_prepareRow($row, $format);

                    $rows[] = $row;
                    $stt++;
                }
            }
            $rowAsm = $this->_prepareRow($rowAsm, $format);
            $rows[] = $rowAsm;

            $asmRows[$asmId] = $rows;
        }
        return compact('headers', 'asmRows', 'rowColumns');
    }

    public function dataAsmRevenueDetail(array $requestParams): array
    {
        $searchParams = $this->parseSearchParams($requestParams);
        $details      = $this->repository->getRevenueTDVDetail($searchParams);
        // HEADER
        $arrTdv = $details['users'];

        // ROWS
        $rows          = [];
        $userTotalInfo = [];
        $finalQty      = 0;
        $finalPrice    = 0;
        $finalDiscount = 0;
        $finalAmount   = 0;

        foreach ($details['products'] as $productId => $productValue) {
            $row          = [];
            $productPrice = $productValue['price'] ?? 0;

            $row[] = $productValue['name'];
            $row[] = $productPrice;

            $totalQty        = 0;
            $totalPrice      = 0;
            $totalDiscount   = 0;
            $totalFinalPrice = 0;

            foreach ($details['users'] as $userId => $username) {
                $qty        = $details['results'][$productId][$userId]['qty'] ?? 0;
                $price      = $qty * $productPrice;
                $discount   = $details['results'][$productId][$userId]['total_discount'] ?? 0;
                $lastPrice = $price - $discount;

                if (isset($userTotalInfo[$userId])) {
                    $userTotalInfo[$userId]['qty']        += $qty;
                    $userTotalInfo[$userId]['price']      += $price;
                    $userTotalInfo[$userId]['discount']   += $discount;
                    $userTotalInfo[$userId]['finalPrice'] += $lastPrice;
                } else {
                    $userTotalInfo[$userId] = [
                        'qty'        => $qty,
                        'price'      => $price,
                        'discount'   => $discount,
                        'finalPrice' => $lastPrice,
                    ];
                }

                $totalQty        += $qty;
                $totalPrice      += $price;
                $totalDiscount   += $discount;
                $totalFinalPrice += $lastPrice;

                $row[] = $qty;
                $row[] = $price;
                $row[] = $discount;
                $row[] = $lastPrice;
            }
            $row[] = $totalQty;
            $row[] = $totalPrice;
            $row[] = $totalDiscount;
            $row[] = $totalFinalPrice;

            $finalQty      += $totalQty;
            $finalPrice    += $totalPrice;
            $finalDiscount += $totalDiscount;
            $finalAmount   += $totalFinalPrice;

            $rows[] = $row;
        }

        $totalRow = [
            'Tổng', ''
        ];
        foreach ($details['users'] as $userId => $userName) {
            $totalRow[] = $userTotalInfo[$userId]['qty'];
            $totalRow[] = $userTotalInfo[$userId]['price'];
            $totalRow[] = $userTotalInfo[$userId]['discount'];
            $totalRow[] = $userTotalInfo[$userId]['finalPrice'];
        }
        $totalRow[] = $finalQty;
        $totalRow[] = $finalPrice;
        $totalRow[] = $finalDiscount;
        $totalRow[] = $finalAmount;

        $rows[] = $totalRow;

        return [
            'arrTdv' => $arrTdv,
            'data'   => $rows,
        ];
    }

    function _prepareRow(array $row, bool $format): array
    {
        foreach ($row as $key => $value) {
            if (!Str::startsWith($key, '_')) {
                if (isset($row['_' . $key])) {
                    $row[$key]['value'] = $format ? Helper::formatPrice($row['_' . $key]) : $row['_' . $key];
                }
            }
        }
        return $row;
    }

    /**
     * @param array $requestParams
     * @return array
     */
    public function detailRevenue(array $requestParams): array
    {
        $searchParams = $this->parseSearchParams($requestParams);
        $details      = $this->repository->getRevenueTDVDetail($searchParams);
        // HEADER
        $header = [
            ['value' => 'Sản phẩm'],
            ['value' => 'Giá tiền(đ)', 'attributes' => 'class="text-end"'],
            ['value' => 'Số lượng', 'attributes' => 'class="text-end"'],
            ['value' => 'Tổng tiền trước CK(đ)', 'attributes' => 'class="text-end"'],
            ['value' => 'Chiết khấu(đ)', 'attributes' => 'class="text-end"'],
            ['value' => 'Tổng tiền sau CK(đ)', 'attributes' => 'class="text-end"'],
        ];

        // ROWS
        $rows                 = [];
        $totalProductQty      = 0;
        $totalProductPrice    = 0;
        $totalProductDiscount = 0;
        $totalFinalPrice      = 0;

        foreach ($details['products'] as $productId => $productValue) {
            $row          = collect([]);
            $productPrice = $productValue['price'] ?? 0;

            $row->push(['value' => $productValue['name'], 'attributes' => 'class="text-nowrap" ' . $productId]);
            $row->push(['value' => Helper::formatPrice($productPrice), 'attributes' => 'class="text-end"']);
            foreach ($details['users'] as $userId => $username) {
                $qty           = $details['results'][$productId][$userId]['qty'] ?? 0;
                $totalPrice    = $productPrice * $qty;
                $totalDiscount = $details['results'][$productId][$userId]['total_discount'] ?? 0;
                $finalPrice    = $totalPrice - $totalDiscount;

                $totalProductQty      += $qty;
                $totalProductPrice    += $totalPrice;
                $totalProductDiscount += $totalDiscount;
                $totalFinalPrice      += $finalPrice;

                $row->push([
                    'value'      => $qty ? Helper::formatPrice($qty) : '',
                    'attributes' => 'class="text-end"'
                ]);
                $row->push(['value' => Helper::formatPrice($productPrice * $qty), 'attributes' => 'class="text-end"']);
                $row->push(['value' => Helper::formatPrice($totalDiscount), 'attributes' => 'class="text-end"']);
                $row->push(['value' => Helper::formatPrice($finalPrice), 'attributes' => 'class="text-end"']);
            }

            $rows[] = $row;
        }

        $rowTotal = collect([
            ['value' => 'Doanh thu', 'attributes' => 'class="text-nowrap fw-bolder"'],
            ['value' => '', 'attributes' => 'class="text-end"'],
            ['value' => $totalProductQty, 'attributes' => 'class="text-end"'],
            ['value' => Helper::formatPrice($totalProductPrice), 'attributes' => 'class="text-end"'],
            ['value' => Helper::formatPrice($totalProductDiscount), 'attributes' => 'class="text-end"'],
            ['value' => Helper::formatPrice($totalFinalPrice), 'attributes' => 'class="text-end"'],
        ]);
        $rowTotal = $rowTotal->map(function ($item) {
            if (isset($item['revenue'])) {
                $item['value'] = Helper::formatPrice($item['revenue']);
            }

            return $item;
        });

        $rows[] = $rowTotal;

        return compact('header', 'rows');
    }

    public function summaryExportOptions(): array
    {
        $filename = '5.1.1_DOANH_SO_TDV_' . Carbon::now()->timestamp . ".xlsx";
        return [
            'hash_id'        => request('hash_id', ''),
            'file_name'      => $filename,
            'file_dir'       => 'download_bao_cao_doanh_thu_tdv',
            'route_download' => route('admin.file.action', [
                'type'     => 'download',
                'folder'   => 'download_bao_cao_doanh_thu_tdv',
                'nameFile' => $filename,
            ]),
            'header_multi'   => true,
        ];
    }

    public function summaryExport($requestParams): array
    {
        $summaryRevenue = $this->summaryRevenue($requestParams, false);
        list('file_name' => $filename, 'file_dir' => $fileDir, 'route_download' => $routeDownload,) = $this->summaryExportOptions();
        $totalRow = collect($summaryRevenue['asmRows'])->flatten(1)->count();

        Excel::store(new TDVSummaryExport($summaryRevenue), $fileDir . '/' . $filename);
        return [
            'done'          => true,
            'total'         => $totalRow,
            'processed'     => $totalRow,
            'percent'       => 100,
            'file_name'     => $filename,
            'current_step'  => 1,
            'started_at'    => now()->format('Y-m-d H:i:s'),
            'download'      => $routeDownload,
            'progress_info' => sprintf('<span>File:</span><span><a class="text-success" href="%s">%s</a></span>', $routeDownload, $filename),
        ];
    }

    public function asmRevenueDetailExport($requestParams)
    {
        $detailRevenue = $this->dataAsmRevenueDetail($requestParams);
        $arrTdv        = $detailRevenue['arrTdv'];
        $data          = $detailRevenue['data'];
        $filename = '5.1.1_CHI_TIET_DOANH_SO_ASM_' . Carbon::now()->timestamp . ".xlsx";

        return Excel::download(new AsmRevenueDetailExport($arrTdv, $data), $filename);
    }

    public function detailExport($requestParams): array
    {
        list('headers' => $headers, 'rows' => $rows) = $this->detailRevenue($requestParams);
        list('file_name' => $filename, 'base_folder' => $baseFolder,
            'file_dir' => $fileDir, 'route_download' => $routeDownload,) = $this->summaryExportOptions();

        Excel::store(new TDVSummaryExport($headers, $rows), $baseFolder . '/' . $fileDir . '/' . $filename);
        return [
            'done'          => true,
            'total'         => count($rows),
            'processed'     => count($rows),
            'percent'       => 100,
            'file_name'     => $filename,
            'current_step'  => 1,
            'started_at'    => now()->format('Y-m-d H:i:s'),
            'download'      => $routeDownload,
            'progress_info' => sprintf('<span>File:</span><span><a class="text-success" href="%s">%s</a></span>', $routeDownload, $filename),
        ];
    }

    public function parseSearchParams($requestParams): array
    {
        $searchBookingAt = $requestParams['search']['booking_at'] ?? implode(' to ', Helper::defaultMonthFromToDate());
        $searchBookingAt = $this->handleDateRangeData($searchBookingAt);

        $divisionId  = $requestParams['search']['division_id'] ?? 0;
        $localityIds = $requestParams['search']['locality_id'] ?? [];
        if ($divisionId && !$localityIds) {
            $localityIds = $this->organizationRepository->getLocalityByDivision($divisionId)->pluck('id')->toArray();
        } elseif (!$divisionId && $localityIds) {
            $localityIds = [];
        }

        return [
            'fromDate'    => $searchBookingAt['from'],
            'toDate'      => $searchBookingAt['to'],
            'asm_user_id' => $requestParams['search']['asm_user_id'] ?? 0,
            'user_id'     => $requestParams['search']['user_id'] ?? 0,
            'locality_id' => (array)$localityIds,
        ];
    }
}
