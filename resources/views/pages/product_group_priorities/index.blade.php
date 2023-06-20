<?php

use \App\Helpers\Helper;

//$options       = [];
//$periodOptions = $formOptions['periods'] ?? [];
//foreach ($periodOptions as $key => $period) {
//    $options[$key] = $period['name'];
//}
$currentUser = Helper::currentUser();
$canEdit     = $currentUser->can('sua_nhom_va_san_pham_uu_tien');
?>
@extends('layouts.main')
@push('content-header')
    <div class="col ms-auto">
        @include('component.btn-add', ['title' => 'Thêm mới', 'href' => route('admin.product-group-priorities.create')])
    </div>
@endpush
@section('content')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/base/pages/product_group_priorities/list.css') }}">
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            {!!
                            \App\Helpers\SearchFormHelper::getForm(
                                route('admin.product-group-priorities.index'),
                                'GET',
                                [
                                    [
                                        "id" => "select_product_type",
                                        "type" => "selection",
                                        "name" => "search[product_type]",
                                        "defaultValue" => request('search.product_type', null),
                                        "options" => $formOptions['product_types'],
                                        "class" => "col",
                                    ],
                                    [
                                        "id" => "select_product",
                                        "type" => "select2",
                                        "name" => "search[product_id]",
                                        "class" => "col",
                                        "defaultValue" => request('search.product_id', 0),
                                        "options" => $formOptions['option_products'],
                                    ],
                                    [
                                        "id" => "select_product",
                                        "type" => "selection",
                                        "name" => "search[store_type]",
                                        "class" => "col",
                                        "defaultValue" => request('search.store_type', 0),
                                        "options" => $formOptions['store_type'],
                                    ],
                                    [
                                        "id" => "select_product",
                                        "type" => "selection",
                                        "name" => "search[region_apply]",
                                        "class" => "col",
                                        "defaultValue" => request('search.region_apply', 0),
                                        "options" => $formOptions['region_apply'],
                                    ],
                                    [
                                        "type" => "multipleSelect2",
                                        "name" => "search[period][]",
                                        "defaultValue" => request('search.period') ?? [],
                                        "id" => "form-period",
                                        "class" => "col",
                                        "options" => $periods,
                                        "placeholder" => " -- Chọn chu kỳ -- ",
                                    ],
                                ]
                            )
                        !!}
                        </div>
                    </div>

                    <div class="table-priority mt-2">
                        @if($results)
                            @foreach($results as $_priorityKey => $_priority)
                                <div class="d-flex w-100 priority-item wr-chuky">
                                    <div class="text-start" style="min-width: 400px; max-width: 400px;">{!! $_priorityKey !!} </div>
                                    <div class="w-100">
                                        @foreach($_priority as $_groupKey => $_group)
                                            <div class="d-flex wr-group" @if(count($_group) == 1) style="min-height: 90px" @endif>
                                                <div class="group">{{ $_groupKey }}</div>
                                                <div class="w-100">
                                                    @foreach($_group as $_subgroupKey => $_subgroup)
                                                        <div class="d-flex wr-sub_group" @if(count($_group) == 1) style="min-height: 90px" @endif>
                                                            <div class="sub_group">{{ $_subgroupKey }}</div>
                                                            <div class="w-100 product" @if(count($_group) == 1) style="min-height: 90px" @endif>
                                                                @foreach($_subgroup as $_product)
                                                                    @if($canEdit)
                                                                        <a class="text-nowrap
                                                                            me-1
                                                                            badge
                                                                            rounded-3
                                                                            margin-top-desktop-05
                                                                            margin-top-mobile
                                                                            @if($_product['priority'])
                                                                            {{ $_product['statusLog'] ? 'bg-success' : 'bg-secondary' }}
                                                                            @else bg-warning @endif "
                                                                           href="{{ route('admin.product-group-priorities.edit', $_product['productGroupPrioritiesId']) }}">
                                                                            {{ $_product['name'] }}
                                                                        </a>
                                                                    @else
                                                                        <div
                                                                            class="text-nowrap me-1 badge bg-secondary rounded-3">
                                                                            {{ $_product['name'] }}
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
