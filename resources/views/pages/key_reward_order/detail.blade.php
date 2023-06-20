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
                    <h1>
                        {{$title_table}}
                    </h1>

{{--                    <div class="card-body">--}}
{{--                        {!! \App\Helpers\SearchFormHelper::getForm(--}}
{{--                            route('admin.report.pharmacy-revenue.detail'),--}}
{{--                            'GET',--}}
{{--                            [--}}
{{--                                     [--}}
{{--                                        'type'         => 'v2DateRangePicker',--}}
{{--                                        'placeholder'  => 'Ngày nhập from - to',--}}
{{--                                        'name'         => 'search[from_to]',--}}
{{--                                        'defaultValue' => $from_to,--}}
{{--//                                        'id'           => 'searchRange',--}}
{{--                                    ],--}}

{{--                                    ],--}}
{{--//                            $indexOptions['searchOptions'],--}}
{{--//                            useExport: request()->user()->can('download_don_nhap_dai_ly'),--}}
{{--//                            routeExport: 'admin.report.export-pharmacy-revenue',--}}
{{--//                            permissionExport: 'xem_bao_cao_doanh_thu_nha_thuoc'--}}
{{--                        ) !!}--}}
{{--                    </div>--}}
                    {!! $table->getTable() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
