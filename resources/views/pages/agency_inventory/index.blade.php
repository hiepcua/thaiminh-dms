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
                        <div class="row flex-row-reverse">

                            <div class="col">
                                <?php
                                $currentDate = \Carbon\Carbon::now();
                                $yearOption  = [];
                                $monthOption = [];

                                for ($i = 1; $i <= 12; $i++) {
                                    $monthOption[$i] = $i;
                                }

                                for ($j = $currentDate->year - 5; $j <= $currentDate->year; $j++) {
                                    $yearOption[$j] = $j;
                                }
                                ?>
                                {!!
                                    \App\Helpers\SearchFormHelper::getForm(
                                        route('admin.agency-inventory.index'),
                                        'GET',
                                        [
											[
                                                "type" => "selection",
                                                "name" => "search[year]",
                                                "defaultValue" => request('search.year', $currentDate->year),
                                                "options" => $yearOption,
                                                "id" => "form-year"
                                            ],
                                            [
                                                "type" => "selection",
                                                "name" => "search[month]",
                                                "defaultValue" => request('search.month', $currentDate->month),
                                                "options" => $monthOption,
                                                "id" => "form-month"
                                            ],
                                            [
                                                "type" => "text",
                                                "name" => "search[codeOrName]",
                                                "placeholder" => "Mã/Tên đại lý",
                                                "defaultValue" => request('search.codeOrName'),
                                            ],
                                            [
                                                "type" => "text",
                                                "name" => "search[codeOrNameProduct]",
                                                "placeholder" => "Mã/Tên sản phẩm",
                                                "defaultValue" => request('search.codeOrNameProduct'),
                                            ],
                                            [
                                                "type" => "divisionPicker",
                                                "divisionPickerConfig" => [
                                                    "currentUser" => \App\Helpers\Helper::currentUser(),
                                                    "hasRelationship" => true,
                                                    "activeTypes" => [
                                                        Organization::TYPE_DIA_BAN,
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
                                                "type" => "selection",
                                                "name" => "search[product_type]",
                                                "defaultValue" => request('search.product_type', null),
                                                "options" => $formOptions['productTypes'] ?? [],
                                                "id" => "form-product_type"
                                            ],
                                            [
                                                "type" => "selection",
                                                "name" => "search[status]",
                                                "defaultValue" => request('search.status', null),
                                                "options" => $formOptions['inventoryStatuses'] ?? [],
                                                "id" => "form-status"
                                            ],
                                        ],
                                        useExport: request()->user()->can('download_danh_sach_hang_ton_dai_ly'),
                                        routeExport: 'admin.agency-inventory.export',
                                        permissionExport: 'download_danh_sach_hang_ton_dai_ly'
                                    )
                                !!}
                            </div>
                        </div>
                    </div>

                    {!! $agencyInventoryTable->getTable() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/core/pages/agency/index.js') }}"></script>
    <script>
        $(document).ready(function () {
            const ROUTE_SAVE_INVENTORY = '{{ route('admin.agency-inventory.save-inventory') }}';
            $('.btn-save-inventory').on('click', function () {
                Swal.fire({
                    title: 'Bạn có chắc chắn muốn kết chuyển',
                    showCancelButton: true,
                    confirmButtonText: 'Kết Chuyển',
                    cancelButtonText: 'Hủy',
                }).then((result) => {
                    if (result.isConfirmed) {
                        ajax(ROUTE_SAVE_INVENTORY, 'post', {
                            agency_id: $(this).attr('data-agency'),
                            product_id: $(this).attr('data-product'),
                            month: $(this).attr('data-month'),
                            year: $(this).attr('data-year'),
                        })
                            .then(function(response) {
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
                            .catch(function(error) {
                                let message = '';
                                let code = error.status;

                                if(code == 422) {
                                    message = Object.values(error.responseJSON.errors)[0];
                                } else {
                                    message = error.responseJSON.message;
                                }

                                console.log(error, code)
                                Swal.fire({
                                    position: 'center',
                                    icon: 'error',
                                    title: message,
                                    showConfirmButton: false,
                                    timer: 1500
                                })
                            })
                    }
                })
            })
        })
    </script>
@endpush
