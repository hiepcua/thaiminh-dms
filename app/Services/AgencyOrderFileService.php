<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Helpers\TableHelper;
use App\Models\Organization;
use App\Models\StoreOrderItem;
use App\Repositories\Agency\AgencyRepositoryInterface;
use App\Repositories\AgencyOrderFile\AgencyOrderFileRepositoryInterface;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\StoreOrder\StoreOrderRepositoryInterface;
use App\Services\BaseService;
use App\Models\AgencyOrderFile;
use Illuminate\Support\Facades\Storage;

class AgencyOrderFileService extends BaseService
{
    protected $repository;
    protected $organizationRepository;
    protected $agencyRepository;
    protected $storeOrderRepository;

    public function __construct(AgencyOrderFileRepositoryInterface $repository,
                                OrganizationRepositoryInterface    $organizationRepository,
                                AgencyRepositoryInterface          $agencyRepository,
                                StoreOrderRepositoryInterface      $storeOrderRepository
    )
    {
        parent::__construct();

        $this->repository             = $repository;
        $this->organizationRepository = $organizationRepository;
        $this->agencyRepository       = $agencyRepository;
        $this->storeOrderRepository   = $storeOrderRepository;
    }

    public function setModel()
    {
        return new AgencyOrderFile();
    }

    public function queryBookingDate()
    {
        return [
            'from' => now()->setDay(1)->format('Y-m-d'),
            'to'   => now()->format('Y-m-d')
        ];
    }

    public function formOption(): array
    {
        $localityId         = request('search.locality_id', null);
        $agencyId           = request('search.agency_id', null);
        $searchBookingAt    = request('search.booking_at', now()->format('Y-m-d'));
        $search_division_id = request('search.division_id', null);
        $orderCode = request('search.order_code', null);
        $localityOptions    = $search_division_id
            ? $this->organizationRepository->getLocalityByDivision($search_division_id)->pluck('name', 'id')->toArray()
            : [];
        $agencyOptions      = $localityId
            ? $this->agencyRepository->getByLocality($localityId)
            : [];

        $searchOptions   = [];
        $searchOptions[] = [
            'type'         => 'text',
            "placeholder"  => "Mã đơn",
            "name"         => "search[order_code]",
            "id"           => "searchCode",
            "defaultValue" => $orderCode
        ];
        $searchOptions[] = [
            'type'         => 'datepicker',
            "placeholder"  => "Ngày mã đơn",
            "name"         => "search[booking_at]",
            "defaultValue" => $orderCode ? null : $searchBookingAt,
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
                    'selected'   => $orderCode ? null : request('search.division_id')
                ]
            ],
        ];
        $searchOptions[] = [
            'type'          => 'selection',
            'name'          => 'search[locality_id]',
            'defaultValue'  => $orderCode ? null : $localityId,
            'id'            => 'form-locality_id',
            'options'       => ['' => '- Địa bàn -'] + $localityOptions,
            'other_options' => ['option_class' => 'ajax-locality-option'],
            'attributes'    => 'style="width: 200px !important"'
        ];
        $searchOptions[] = [
            'type'          => 'selection',
            'name'          => 'search[agency_id]',
            'defaultValue'  => $orderCode ? null : $agencyId,
            'id'            => 'form-agency_id',
            'options'       => ['' => '- Đại lý -'] + $agencyOptions,
            'other_options' => ['option_class' => 'ajax-agency-option'],
            'attributes'    => 'style="width: 200px !important"'
        ];

        return compact('searchOptions');
    }

    protected function handleRequestReportStatementAgency($requestParams = [])
    {
        $requestParams['booking_at'] = $requestParams['booking_at'] ?? now()->format('Y-m-d');

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
                "column" => "agency_order_files.created_at",
                "type"   => "DESC"
            ]]
        ], $showOption);

        $requestParams = $this->handleRequestReportStatementAgency($requestParams);

        if (isset($requestParams['order_code'])) {
            $requestParams = ['order_code' => $requestParams['order_code']];
        }

        $results = $this->repository->getDataForListScreen($requestParams, $showOption);

        $countResult = 0;
        $results->map(function ($result) use (&$countResult) {
            $code = $result->order_code;
            $result->basic_info = "<b class='text-primary'>Mã đơn: </b> $code <br>
                <b class='text-primary'>Tên đại lý: </b> $result->agency_name <br>
                <b class='text-primary'>Số lượng đơn: $result->qty_store_order_merged</b>";
            $result->stt        = ++$countResult;
            $result->tdv_name   = $result->agencyOrder?->creator?->name;
            $result->cost       = Helper::formatPrice($result->cost);
            $result->discount   = Helper::formatPrice($result->discount);
            $result->final_cost = Helper::formatPrice($result->final_cost);
            $fileUrl            = route('admin.file.action', [
                'nameFile' => $result->file_url,
                'folder'   => 'agency_order_files',
                'type'     => 'open'
            ]);
            $requestData = request()->all();
            $requestData['search']['order_code'] = $code;

            $routeExport = route('admin.report.agency-orders.export', array_merge(
                $requestData,
                ['hash_id' => md5('505_' . time() . $result->id)])
            );

            $result->file_url   = '<a class="text-centre" href="' . $fileUrl . '" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-save">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span class="menu-title text-truncate" style="margin-left: 5px" data-i18n="File Manager">In PXK</span>
                </a>
                <button type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#modal_export_' . $result->id . '"
                    data-href="' . $routeExport . '"
                    class="btn btn-icon btn-outline-dark export-js" style="border: none !important;">
                    <i data-feather="file-text"></i>
                    <span class="menu-title text-truncate" style="margin-left: 5px" data-i18n="File Manager">Bản kê</span>
                </button>';

            $result->file_url .= view('snippets.modal-export-progress', ['idExportModal' => "modal_export_" . $result->id ]);
        });

        $nameTable = 'agency-order-file-list';

        return new TableHelper(
            collections: $results,
            nameTable: $nameTable,
        );
    }
}
