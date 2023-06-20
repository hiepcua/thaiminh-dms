<?php
    use App\Models\Organization;
    use App\Helpers\Helper;
?>
@extends('layouts.main')
@section('page_title', $page_title)
@push('content-header')
    @can('them_don_nhap_dai_ly')
        <div class="col ms-auto">
            @include('component.btn-add', ['title'=>'Thêm mới', 'href'=>route('admin.agency-order.create')])
        </div>
    @endcan
@endpush
@section('content')
    <section class="app-user-list">
        <div class="row" id="table-striped">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row flex-row-reverse">

                            <div class="col">

                                    {!!
                                    \App\Helpers\SearchFormHelper::getForm(
                                        route('admin.agency-order.index'),
                                        'GET',
                                        [
                                            [
                                                "type" => "text",
                                                "name" => "search[codeOrName]",
                                                "placeholder" => "Mã/Tên đại lý",
                                                "defaultValue" => request('search.codeOrName'),
                                            ],
                                            [
                                                "type" => "divisionPicker",
                                                "divisionPickerConfig" => [
                                                    "currentUser" => Helper::currentUser(),
                                                    "hasRelationship" => true,
                                                    "activeTypes" => [
                                                        Organization::TYPE_KHU_VUC,
                                                    ],
                                                    "excludeTypes" => [
                                                        Organization::TYPE_DIA_BAN
                                                    ],
                                                    "setup" => [
                                                        'multiple' => false,
                                                        'name' => 'search[division_id]',
                                                        'class' => '',
                                                        'id' => 'division_id',
                                                        'attributes' => '',
                                                        'selected' => request('search.division_id', null)
                                                    ]
                                                ],
                                            ],
                                            [
                                                "type" => "dateRangePicker",
                                                "placeholder" => "Ngày nhập from - to",
                                                "name" => "search[booking_at]",
                                                "defaultValue" => request('search.booking_at'),
                                                "id" => "searchRange",
                                            ],
                                            [
                                                "type" => "selection",
                                                "name" => "search[type]",
                                                "defaultValue" => request('search.type'),
                                                "options" => \App\Models\AgencyOrder::TYPE_TEXTS
                                            ],
                                            [
                                                "type" => "selection",
                                                "name" => "search[status]",
                                                "defaultValue" => request('search.status'),
                                                "options" => \App\Models\AgencyOrder::STATUS_TEXTS
                                            ],
                                        ],
//                                        useExport: request()->user()->can('download_don_nhap_dai_ly'),
//                                        routeExport: 'admin.agency-order.export',
//                                        permissionExport: 'download_don_nhap_dai_ly'
                                    )
                                !!}
                            </div>
                        </div>

                        @can('huy_don_nhap_dai_ly')
                        <button action-delete="{{ route('admin.agency-order.remove-order') }}" class="btn btn-danger btn-icon waves-effect" id="btn-remove-order" type="button">
                            Hủy đơn
                        </button>
                        @endcan
                    </div>

                    {!! $table->getTable() !!}
                </div>
            </div>
        </div>
        </div>
    </section>
@endsection
@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script>
        const ROUTE_CHECK_ALLOW_DELETE_ORDER = "{{ route('admin.agency-order.check-order-allow-delete') }}";
    </script>
    <script src="{{ asset('js/core/pages/agency-order/index.js') }}"></script>
@endpush
