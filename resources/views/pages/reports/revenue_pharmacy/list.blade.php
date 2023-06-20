<?php

use App\Models\Organization;

?>
@extends('layouts.main')
@section('page_title', $page_title)
@section('content')
    <section class="app-user-list">
        <div class="row" id="table-striped">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        {!! \App\Helpers\SearchFormHelper::getForm(
                            route('admin.report.pharmacy-revenue'),
                            'GET',
                            [
                                     [
                                        'type'         => 'v2DateRangePicker',
                                        'placeholder'  => 'Ngày nhập from - to',
                                        'name'         => 'search[from_to]',
                                        'defaultValue' => $indexOptions['default_range'],
//                                        'id'           => 'searchRange',
                                    ],
                                     [
                                    'type'                 => 'divisionPicker',
                                    'divisionPickerConfig' => [
                                        'currentUser'     => true,
                                        'activeTypes'     => [
                                            Organization::TYPE_DIA_BAN,
                                        ],
                                        'excludeTypes'    => [
                                            Organization::TYPE_KHU_VUC,
                                            Organization::TYPE_MIEN,
                                        ],
                                        'hasRelationship' => true,
                                        'setup'           => [
                                            'multiple'   => false,
                                            'name'       => 'search[division_id]',
                                            'class'      => '',
                                            'id'         => 'division_id',
                                            'attributes' => '',
                                            'selected'   =>  request('search.division_id'),
                                            "class" => "col-md-1",
                                        ]
                                    ],
                                ],
                                    [
                                        "type" => "text",
                                        "id" => "searchInput",
                                        "name" => "search[pharmacy_code]",
                                        "defaultValue" => request('search.pharmacy_code'),
                                        "placeholder" => 'Mã/Tên NT',
                                    ],
                                    [
                                        "id" => "select_region",
                                        "type" => "select2",
                                        "name" => "search[region]",
                                        "class" => "col-md-1",
                                        "defaultValue" => request('search.region', ''),
                                        "options" => $indexOptions['region'],
                                    ],
                                    [
                                        "id" => "select_province",
                                        "type" => "select2",
                                        "name" => "search[province_id]",
                                        "class" => "col-md-2",
                                        "defaultValue" => request('search.province_id', ''),
                                        "options" => $indexOptions['option_provinces'],
                                    ],


                                    ],
//                            $indexOptions['searchOptions'],
//                            useExport: request()->user()->can('download_don_nhap_dai_ly'),
                            routeExport: 'admin.report.export-pharmacy-revenue',
                            permissionExport: 'xem_bao_cao_doanh_thu_nha_thuoc'
                        ) !!}
                    </div>

                    {!! $table->getTable() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts-custom')
    <script src="{{ asset('vendors/js/moment/moment.min.js') }}"></script>
    <script src="{{ asset('vendors/js/datepickerV2/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('vendors/js/moment/moment.min.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/css/datepickerV2/daterangepicker.css') }}"/>
    <script>

        $('.daterangepicker_v2').daterangepicker({
                                                     ranges: {
                                                         'Today': [moment(), moment()],
                                                         'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                                                         'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                                                         'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                                                         'This Month': [moment().startOf('month'), moment().endOf('month')],
                                                         'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                                                     },
                                                     "showCustomRangeLabel": false,
                                                     "alwaysShowCalendars": true,
                                                     // "startDate": moment().startOf('month'),
                                                     // "endDate": moment().endOf('month'),
                                                     locale: {
                                                         "format": "YYYY-MM-DD",
                                                         "separator": " to ",
                                                     }
                                                 },
                                                 function (start, end, label) {
                                                     console.log('New date range selected: ' + start.format('DD/MM/YYYY') + ' to ' + end.format('DD/MM/YYYY') + ' (predefined range: ' + label + ')');
                                                 });
    </script>

@endpush
