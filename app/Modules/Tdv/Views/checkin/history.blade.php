<?php
use App\Models\Organization;

$currentPermissions = \App\Helpers\Helper::getCurrentPermissions();
?>
@extends('layouts.main')
@section('page_title', $page_title)
@section('content')
    <section class="app-user-list">
        <div class="row" id="table-striped">
            <div class="col-12">
                <div class="card">
                    <div class="nav-tabs-shadow nav-align-top">
                        <ul class="nav nav-tabs mb-0" role="tablist">
                            <li class="nav-item">
                                <button type="button" class="nav-link @if(request('search.tab', null) == 'history' || request('search.tab', null) == null) active @endif" role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-home" aria-controls="navs-top-home">
                                    Lịch sử checkin
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link @if(request('search.tab', null) == 'request') active @endif" role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-profile" aria-controls="navs-top-profile">
                                    Danh Sách đề xuất
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade  @if(request('search.tab', null) == 'history' || request('search.tab', null) == null) show active @endif" id="navs-top-home" role="tabpanel">
                                @if(in_array('tdv_xem_lich_su_checkin', $currentPermissions))
                                <div class="card-body pb-0">
                                    <div class="row flex-row-reverse">
                                        <div class="col">
                                            <?php
                                            $localities = [
                                                0 => "- Địa bàn -"
                                            ];
                                            foreach(($formOptions['locality_ids'] ?? []) as $item_locality) {
                                                $localities[$item_locality->id] = $item_locality->name;
                                            }
                                            ?>
                                            {!!
                                                \App\Helpers\SearchFormHelper::getForm(
                                                    route('admin.tdv.checkin.histories'),
                                                    'GET',
                                                    [
                                                        [
                                                            "type" => "text",
                                                            "name" => "search[store_name]",
                                                            "placeholder" => "Tên nhà thuốc",
                                                            "defaultValue" => request('search.store_name'),
                                                        ],
                                                        [
                                                            "type" => "text",
                                                            "name" => "search[tab]",
                                                            "defaultValue" => 'history',
                                                            "class" => 'd-none'
                                                        ],
                                                        [
                                                            "type" => "datepicker",
                                                            "placeholder" => "Ngày checkin",
                                                            "name" => "search[checkin_at_date]",
                                                            "defaultValue" => request('search.checkin_at_date') ?? \Carbon\Carbon::now()->format('Y-m-d'),
                                                            "id" => "searchRange",
                                                        ],
                                                    ],
                                                )
                                            !!}
                                        </div>
                                    </div>
                                </div>

                                {!! $table->getTable() !!}
                                @else
                                    <div class="card-body text-center">
                                        <h3 class="mb-0">Bạn không có quyền xem dữ liệu</h3>
                                    </div>
                                @endif
                            </div>
                            <div class="tab-pane fade @if(request('search.tab', null) == 'request') show active @endif" id="navs-top-profile" role="tabpanel">
                                @if(in_array('tdv_xem_danh_sach_de_xuat_quen_checkout', $currentPermissions))
                                    <div class="card-body pb-0">
                                        <div class="row flex-row-reverse">
                                            <div class="col">
                                                    <?php
                                                    $localities = [
                                                        0 => "- Địa bàn -"
                                                    ];
                                                    foreach(($formOptions['locality_ids'] ?? []) as $item_locality) {
                                                        $localities[$item_locality->id] = $item_locality->name;
                                                    }
                                                    ?>
                                                {!!
                                                    \App\Helpers\SearchFormHelper::getForm(
                                                        route('admin.tdv.checkin.histories'),
                                                        'GET',
                                                        [
                                                            [
                                                                "type" => "text",
                                                                "name" => "search[tab]",
                                                                "defaultValue" => 'request',
                                                                "class" => 'd-none'
                                                            ],
                                                            [
                                                                "type" => "text",
                                                                "name" => "search[request_store_name]",
                                                                "placeholder" => "Tên nhà thuốc",
                                                                "defaultValue" => request('search.request_store_name'),
                                                            ],
                                                            [
                                                                "type" => "dateRangePicker",
                                                                "placeholder" => "Ngày đề xuất",
                                                                "name" => "search[request_created_at]",
                                                                "defaultValue" => request('search.request_created_at'),
                                                                "id" => "searchRange",
                                                            ],
                                                            [
                                                                "type" => "selection",
                                                                "name" => "search[request_status]",
                                                                "defaultValue" => request('search.request_status', null),
                                                                "options" => ['' => "Trạng thái"] + \App\Models\ForgetCheckin::STATUS_TEXTS,
                                                            ],
                                                        ],
                                                    )
                                                !!}
                                            </div>
                                        </div>
                                    </div>

                                    {!! $tableRequestCheckin->getTable() !!}
                                @else
                                    <div class="card-body text-center">
                                        <h3 class="mb-0">Bạn không có quyền xem dữ liệu</h3>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts-custom')
    <script>
        $(document).ready(function () {
            const ROUTE_CREATE_REQUEST_CHECKIN = '{{ route('admin.tdv.checkin.forgetCheckout') }}';

            $(document).on('click', '.btn-request-checkin', function () {
                let checkin_id = $(this).attr('checkin-id');
                Swal.fire({
                    title: 'Tạo đề xuất',
                    input: 'textarea',
                    showCancelButton: true,
                    confirmButtonText: 'Tạo',
                    cancelButtonText: 'Hủy',
                    preConfirm: (note) => {
                        return ajax(ROUTE_CREATE_REQUEST_CHECKIN, 'post', {
                            'checkinId': checkin_id,
                            'creatorNote': note
                        }).then(response => {
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function () {
                                window.location.reload();
                            })
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: error.responseJSON.message,
                            })
                        })
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                })
            })
        })
    </script>
@endpush
