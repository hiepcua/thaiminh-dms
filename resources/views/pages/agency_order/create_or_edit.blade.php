<?php
use App\Models\Organization;
?>
@extends('layouts.main')
@section('page_title', $page_title)
@section('content')
    @php($currentRouteName = request()->route()->getName())
    @php($disableInput = $currentRouteName == 'admin.agency-order.show')
    <section class="app-user-list">
        <div class="row" id="table-striped">
            <div class="col-12">
                <div class="card">
                    @if($currentRouteName != 'admin.agency-order.show')
                    <form class="card-body" method="post" action="{{ $formOptions['action'] ?? '' }}">
                        @csrf
                        @if($currentRouteName == 'admin.agency-order.edit')
                            @method('put')
                        @endif
                    @else
                        <form class="card-body">
                    @endif
                        <h3 style="color: #636363">Thông tin chung</h3>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="w-100 mb-1">
                                    <label for="title">
                                        Tiêu đề<span class="text-danger">(*)</span>
                                    </label>
                                    <input @if($disableInput) disabled @endif type="text" id="title" class="form-control" name="title" value="{{ $default_values['title'] ?? '' }}" placeholder="Tiêu đề">
                                </div>
                                <div class="w-100 mb-1">
                                    <label for="locality_id">
                                        Địa bàn
                                    </label>
                                    {!!
                                        \App\Helpers\Helper::getTreeOrganization(
                                            currentUser: \App\Helpers\Helper::currentUser(),
                                            hasRelationship: true,
                                            activeTypes: [
                                                Organization::TYPE_DIA_BAN,
                                            ],
                                            setup: [
                                                'multiple' => false,
                                                'name' => 'locality_id',
                                                'class' => '',
                                                'id' => 'locality_id',
                                                'attributes' =>  $disableInput ? 'disabled' : '',
                                                'selected' => $default_values['locality_id'] ?? null
                                            ]
                                        )
                                    !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="w-100 mb-1">
                                    <label for="booking_at">
                                        Ngày nhập hàng<span class="text-danger">(*)</span>
                                    </label>
                                    <input type="text" name="booking_at"
                                           @if($disableInput) disabled style="background-color: #efefef;" @endif
                                           id="booking_at"
                                           class="form-control flatpickr-basic flatpickr-input"
                                           placeholder="YYYY-MM-DD" value="{{ $default_values['booking_at'] ?? null }}"
                                           readonly="readonly">
                                </div>
                                <div class="w-100 mb-1">
                                    <label for="agency_id">
                                        Đại lý<span class="text-danger">(*)</span>
                                    </label>
                                    <select id="agency_id" @if($disableInput) disabled @endif class="form-control form-organization_id has-select2" name="agency_id">
                                        <option value="">- Lựa chọn -</option>
                                        @foreach($formOptions['agencies'] ?? [] as $key => $agency)
                                            <option
                                                value="{{ $key }}"
                                                @if(($default_values['agency_id'] ?? null) == $key) selected @endif
                                                class="ajax-locality-option">
                                                {{ $agency }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="note" class="w-100">
                                    Ghi chú
                                    <textarea class="form-control" name="note" @if($disableInput) disabled @endif>{{ $default_values['note'] ?? '' }}</textarea>
                                </label>
                            </div>
                        </div>
                        <h3 style="color: #636363" class="mt-2">Sản phẩm</h3>
                        <div class="row" id="input-products">
                        </div>
                        <h3 style="color: #636363" class="mt-2">Chi tiết đơn hàng</h3>
                        <div class="row p-1" id="detail-order">
                            <table class="table table-borderless mb-2">
                                <thead class="table-dark">
                                <tr>
                                    <th style="width: 40px;">STT</th>
                                    <th>Sản phẩm</th>
                                    <th class="text-end">Số lượng</th>
                                    <th class="text-end">Giá</th>
                                    <th class="text-end">Thành tiên</th>
                                    <th style="width: 40px;" class="text-end"></th>
                                </tr>
                                </thead>
                                <tbody id="list-product-order">
                                <tr>
                                    <td>&nbsp;</td>
                                    <td></td>
                                    <td class="text-end"></td>
                                    <td class="text-end"></td>
                                    <td class="text-end"></td>
                                    <td class="text-end"></td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr class="table-dark hidden row-subtotal">
                                    <td colspan="4"><b>Chiết khấu</b></td>
                                    <td class="text-end"><b class="row-subtotal-amount"></b></td>
                                    <td class="text-end"></td>
                                </tr>
                                <tr class="table-dark row-total d-none">
                                    <td colspan="2"><b>Tổng</b></td>
                                    <td class="text-end"><b class="row-total-qty"></b></td>
                                    <td class="text-end"></td>
                                    <td class="text-end"><b class="row-total-amount"></b></td>
                                    <td class="text-end"></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="mt-1">
                            @if($currentRouteName == 'admin.agency-order.create')
                            <button type="submit" class="btn btn-primary">Tạo đơn</button>
                            @endif
                            @if($currentRouteName == 'admin.agency-order.edit')
                            <button type="submit" class="btn btn-primary">Cập nhập</button>
                            @endif

                            <a href="{{ route('admin.agency-order.index') }}" class="btn btn-secondary">Trở lại</a>
                        </div>

                    @if($currentRouteName != 'admin.agency-order.show')
                        </form>
                    @else
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script>
        const ROUTE_GET_AGENCY = "{{ route('admin.get-agency-by-locality') }}";
        const DISABLE_INPUT = {{ $disableInput ? "true" : "false" }};
        const ROUTE_GET_PRODUCT_GROUPED = "{{ route('admin.get-product-grouped') }}";
        let PRODUCTS = @json($formOptions['products']);
        let OLD_PRODUCTS = @if(isset($formOptions['old_products'])) @json($formOptions['old_products']) @else [] @endif;
        console.log(OLD_PRODUCTS, 'init')
    </script>
    <script src="{{ asset('js/core/pages/agency-order/create-or-edit.js') }}"></script>
@endpush
