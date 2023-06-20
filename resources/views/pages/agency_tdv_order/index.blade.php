<?php
    use App\Models\Organization;
    use App\Helpers\Helper;
?>
@extends('layouts.main')
@section('page_title', $page_title)
@push('content-header')
    @can('them_don_nhap_tdv_toi_dai_ly')
        <div class="col ms-auto">
            @include('component.btn-add', ['title'=>'Đại lý thanh toán', 'class' => "btn-add-agency-order disabled"])
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
                                        route('admin.agency-order-tdv.index'),
                                        'GET',
                                        [
                                            [
                                                "type" => "text",
                                                "name" => "search[codeOrName]",
                                                "class" => "normal-condition",
                                                "placeholder" => "Mã/Tên đại lý",
                                                "defaultValue" => request('search.codeOrName'),
                                            ],
                                            [
                                                "type" => "text",
                                                "name" => "search[agencyCode]",
                                                "id" => "condition-agency-code",
                                                "placeholder" => "Mã đơn TT",
                                                "defaultValue" => request('search.agencyCode'),
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
                                                        'class' => 'normal-condition',
                                                        'id' => 'division_id',
                                                        'attributes' => '',
                                                        'selected' => request('search.division_id', null)
                                                    ]
                                                ],
                                            ],
                                            [
                                                "type" => "dateRangePicker",
                                                "placeholder" => "Ngày nhập from - to",
                                                "class" => "normal-condition",
                                                "name" => "search[booking_at]",
                                                "defaultValue" => request('search.booking_at'),
                                                "id" => "searchRange",
                                            ],
                                            [
                                                "type" => "selection",
                                                "name" => "search[status]",
                                                "class" => "normal-condition",
                                                "defaultValue" => request('search.status'),
                                                "options" => \App\Models\StoreOrder::STATUS_TEXTS
                                            ],
                                            [
                                                "type" => "selection",
                                                "name" => "search[agency_status]",
                                                "class" => "normal-condition",
                                                "defaultValue" => request('search.agency_status') ?? \App\Models\StoreOrder::AGENCY_STATUS_CHUA_THANH_TOAN,
                                                "options" => \App\Models\StoreOrder::AGENCY_STATUS_TEXT
                                            ],
                                        ],
                                        useExport: request()->user()->can('download_don_tdv_toi_dai_ly'),
                                        routeExport: 'admin.agency-order-tdv.export',
                                        permissionExport: 'download_don_tdv_toi_dai_ly'
                                    )
                                !!}
                            </div>
                        </div>
                    </div>

                    <form method="post" action="{{ route('admin.agency-order-tdv.validate-before-create') }}" id="form-create-agency-order">
                        @csrf
                        {!! $table->getTable() !!}
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script>
        const ROUTE_CHECK_ALLOW_CREATE_ORDER = "{{ route('admin.agency-order-tdv.check-order-allow-create') }}";
    </script>
    <script src="{{ asset('js/core/pages/agency-order-tdv/index.js') }}"></script>
@endpush
