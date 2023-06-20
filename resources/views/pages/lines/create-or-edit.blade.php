<?php

use App\Models\Organization;

?>
@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body">
            <form id="form-add-edit-line" method="post" action="{{ $formOptions['action'] }}"
                  enctype="multipart/form-data">
                @csrf
                @if(request()->route()->getName() == 'admin.lines.edit')
                    @method('PUT')
                @endif

                <div class="col-12 col-md-12 mb-1">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-2">Địa bàn: <span class="text-danger">(*)</span></div>
                        <div class="col-12 col-md-8">
                            {!!
                                \App\Helpers\Helper::getTreeOrganization(
                                    currentUser: \App\Helpers\Helper::currentUser(),
                                    hasRelationship: true,
                                    activeTypes: [
                                        Organization::TYPE_DIA_BAN,
                                    ],
                                    setup: [
                                        'multiple' => false,
                                        'name' => 'locality',
                                        'class' => '',
                                        'id' => 'form-locality',
                                        'attributes' =>  $formOptions['isDisabled'] ?? null,
                                        'selected' => $default_values['organization_id'] ?? null
                                    ]
                                )
                            !!}
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-12 mb-1">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-2">Tên tuyến: <span class="text-danger">(*)</span></div>
                        <div class="col-12 col-md-8">
                            <input type="text" id="form-name" class="form-control" name="name"
                                   value="{{ $default_values['name'] ?? '' }}"
                                   placeholder="Tên tuyến" {{ $formOptions['isDisabled'] }} required>
                        </div>
                    </div>
                </div>

                @if(isset($formOptions['productGroupDays']))
                    @foreach($formOptions['productGroupDays'] as $group)
                        <div class="col-12 col-md-12 mb-1">
                            <div class="row align-items-center">
                                <div class="col-12 col-md-2">{{ $group->name }} (thứ):</div>
                                <div class="col-12 col-md-10">
                                    <div class="demo-inline-spacing">
                                        @if(isset($group->days))
                                            @foreach($group->days as $indexDay => $day)
                                                @php $valDay = $group->id .'-'. $indexDay; @endphp
                                                <div class="form-check form-check-inline mt-0">
                                                    <input class="form-check-input check-group" type="checkbox"
                                                           id="chk-group{{ $group->id }}-t{{ $indexDay }}"
                                                           name="day_of_week[]"
                                                           {{ in_array($valDay, $formOptions['lineGroups']) ? 'checked' : '' }}
                                                           value="{{ $valDay }}"
                                                        {{ $formOptions['isDisabled'] }}>
                                                    <label class="form-check-label"
                                                           for="chk-group{{ $group->id }}-t{{ $indexDay }}">{{ $day }}</label>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                <div
                    class="col-12 col-md-12 mb-2 wrapper-list-store {{ !isset($default_values['organization_id']) ? 'd-none' : null }}">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div>Nhà thuốc đã chọn:</div>
                            <div class="input-group input-group-merge mb-1">
                                <input type="text" id="input-filter-store" class="form-control"
                                       placeholder="Mã/ Tên nhà thuốc"
                                       aria-describedby="basic-addon-search2" {{ $formOptions['isDisabled'] }}>
                                <span class="input-group-text" id="basic-addon-search2"><i
                                        data-feather='search'></i></span>
                            </div>
                            <div id="wg-store" class="table-responsive">
                                <table id="table-store" class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Thông tin nhà thuốc</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                    </thead>
                                    <tbody id="table-store-tbody">
                                    @if(isset($formOptions['storesRunning']))
                                        @foreach($formOptions['storesRunning'] as $store)
                                            <tr id="store-{{ $store->id }}" class="store">
                                                <td class="info">{{ $store->id.'-'.$store->code . ' - ' . $store->name . ' - '. $store->address }}</td>
                                                <td class="text-center">
                                                    <input type="hidden" name="stores[]" value="{{ $store->id }}">
                                                    <a href="javascript:void(0)"
                                                       class="btn btn-secondary btn-sm btn-remove"
                                                       onclick="removeStore(this)" data-store-id="{{ $store->id }}"><i
                                                            data-feather="trash-2"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div>Nhà thuốc theo địa bàn chưa thiết lập tuyến:</div>
                            <div id="wg-store-locality" class="table-responsive">
                                <div class="input-group input-group-merge mb-1">
                                    <input type="text" id="input-filter-store-locality" class="form-control"
                                           placeholder="Mã/ Tên nhà thuốc"
                                           aria-describedby="basic-addon-filter-store-locality" {{ $formOptions['isDisabled'] }}>
                                    <span class="input-group-text" id="basic-addon-filter-store-locality"><i
                                            data-feather='search'></i></span>
                                </div>

                                <div class="height500">
                                    <table id="table-store-locality" class="table table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>THÔNG TIN NHÀ THUỐC</th>
                                        </tr>
                                        </thead>
                                        <tbody id="table-store-locality-tbody">
                                        @if (isset($formOptions['localityFreeStores']))
                                            @foreach ($formOptions['localityFreeStores'] as $key => $store)
                                                <tr id="store-locality-{{ $store->id }}" class="store-locality"
                                                    onclick="checkedStore(this)"
                                                    data-store-id="{{ $store->id }}">
                                                    <td class="text-center">
                                                        <div class="form-check">
                                                            <input class="form-check-input chk-store" type="checkbox"
                                                                   name="free-store[]"
                                                                   value="{{ $store->id }}" disabled>
                                                        </div>
                                                    </td>
                                                    <td class="info">{{ $store->id.'-'.$store->code . ' - ' . $store->name . ' - '. $store->address }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="4">Không có dữ liệu</td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-center">
                    @if(!$formOptions['isDisabled'])
                        <button id="form-btn-save" type="submit" class="btn btn-success me-1">
                            {{ request()->route()->getName() == 'admin.lines.edit' ? 'Cập nhật' : 'Tạo mới' }}
                        </button>
                    @endif

                    <a href="{{ route('admin.lines.index') }}" class="btn btn-secondary me-1"><i
                            data-feather='rotate-ccw'></i> Quay lại</a>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts-custom')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/pages/line.css') }}">
    <script>
        const GET_STORE_LOCALITY = '{{ route('admin.get-store-locality') }}';
        $(document).ready(function () {
            $('#form-add-edit-line').on("keypress", function (e) {
                let code = e.keyCode || e.which;
                if (code == 13) {
                    e.preventDefault();
                    return false;
                }
            });

            let formLocality = $('#form-locality'),
                inputFilterStore = $('#input-filter-store'),
                tableStoreTbody = $('#table-store-tbody'),
                tableStoreLocalityTbody = $('#table-store-locality-tbody'),
                inputFilterStoreLocality = $('#input-filter-store-locality')

            formLocality.on('change', function () {
                let locality_id = $(this).val() ?? '';
                if (locality_id) {
                    $('.wrapper-list-store').removeClass('d-none');
                    ajax(GET_STORE_LOCALITY, 'GET', {
                        locality_id: locality_id
                    }).done(function (response) {
                        tableStoreLocalityTbody.html(response.htmlString);
                        tableStoreTbody.empty();
                        if (feather) {
                            feather.replace({
                                width: 14,
                                height: 14
                            });
                        }
                    }).fail(function (error) {
                        console.log(error);
                        alert('Server has an error. Please try again!');
                    });
                } else {
                    resetAllStore();
                }
            });

            inputFilterStore.on('keyup', filterStore);

            inputFilterStoreLocality.on('keyup', filterStoreLocality);
        });

        function filterStore() {
            // Declare variables
            let input, filter, ul, li, a, i, txtValue;
            input = document.getElementById('input-filter-store');
            filter = input.value.toUpperCase();
            ul = document.getElementById("table-store-tbody");
            li = ul.getElementsByClassName('store');

            // Loop through all list items, and hide those who don't match the search query
            for (i = 0; i < li.length; i++) {
                a = li[i].getElementsByClassName("info")[0];
                txtValue = a.textContent || a.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    li[i].style.display = "";
                } else {
                    li[i].style.display = "none";
                }
            }
        }

        function filterStoreLocality() {
            // Declare variables
            let input, filter, ul, li, a, i, txtValue;
            input = document.getElementById('input-filter-store-locality');
            filter = input.value.toUpperCase();
            ul = document.getElementById("table-store-locality-tbody");
            li = ul.getElementsByClassName('store-locality');

            // Loop through all list items, and hide those who don't match the search query
            for (i = 0; i < li.length; i++) {
                a = li[i].getElementsByClassName("info")[0];
                txtValue = a.textContent || a.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    li[i].style.display = "";
                } else {
                    li[i].style.display = "none";
                }
            }
        }

        function removeStore(e) {
            let storeId = e.getAttribute('data-store-id');
            e.parentNode.parentNode.remove();
            $('#store-' + storeId).find('.chk-store').prop('checked', false);
            $('#store-locality-' + storeId).find('.chk-store').prop('checked', false);
        }

        function checkedStore(e) {
            let _store = e,
                _storeId = e.getAttribute('data-store-id'),
                _chk = e.querySelector('.chk-store'),
                _info = _store.querySelector('.info') ? _store.querySelector('.info').textContent : '';

            if (!_chk.checked) {
                _chk.checked = true;
                let _html = '<tr id="store-' + _storeId + '" class="store">' +
                    '<td class="info">' + _info + '</td>' +
                    '<td class="text-center">' +
                    '<input type="hidden" name="stores[]" value="' + _storeId + '">' +
                    '<a href="javascript:void(0)" class="btn btn-secondary btn-sm btn-remove" onclick="removeStore(this)" data-store-id="' + _storeId + '"><i data-feather="trash-2"></i></a>' +
                    '</td>' +
                    '</tr>';
                $('#table-store-tbody').append(_html);
                if (feather) {
                    feather.replace({
                        width: 14,
                        height: 14
                    });
                }
            } else {
                _chk.checked = false;
                $('#store-' + _storeId).remove();
            }
        }

        function resetAllStore() {
            $('#table-store-tbody').empty();
            $('#table-store-locality-tbody').empty();
            $('.wrapper-list-store').addClass('d-none');
        }
    </script>
@endpush
