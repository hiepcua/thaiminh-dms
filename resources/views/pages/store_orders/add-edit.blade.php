@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body">
            <form id="form-store-orders" class="" method="post" action="{{ $formOptions['action'] }}">
                @csrf
                @if($order_id)
                    @method('put')
                @endif
                <div class="alert-block mb-1" style="display: none"></div>

                <div class="content-header mb-1">
                    <h4 class="text-success">Thông tin chung</h4>
                </div>
                <div id="wrapper-info">
                    @if(!$formOptions['is_tdv'])
                        <div class="row">
                            <div class="col-12 col-md-6 mb-1">
                                <div class="row align-items-center">
                                    <label class="col-12 col-md-4" for="form-booking_at">Ngày nhập hàng:</label>
                                    <div class="col-12 col-md-8">
                                        <input type="text" id="form-booking_at" class="form-control flatpickr-basic"
                                               placeholder="YYYY-MM-DD"
                                               name="booking_at"
                                               value="{{ $default_values['booking_at'] }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 mb-1">
                                <div class="row align-items-center">
                                    <label class="col-md-4" for="form-organization_id">Địa bàn:</label>
                                    <div class="col-12 col-md-8">
                                        {!! $formOptions['organization'] !!}
                                        <div><span id="form-organization_id-error" class="error"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{--                    <input type="hidden" name="organization_id" value="{{ $default_values['organization_id'] }}">--}}
                    @endif
                    <div class="row">
                        <div class="col-12 col-md-6 mb-1">
                            <div class="row align-items-center">
                                <label class="col-md-4" for="form-store_id">Nhà thuốc:</label>
                                <div class="col-12 col-md-8">
                                    <select id="form-store_id" name="store_id"
                                            class="form-control has-select2 control-order-setup-area"
                                            aria-describedby="form-store_id-error"
                                            required>
                                        <option value="">- Nhà thuốc -</option>
                                        @foreach( $formOptions['stores'] as $_id => $_name )
                                            <option
                                                value="{{ $_id }}" {{ $default_values['store_id'] == $_id ? 'selected' : '' }}>
                                                {{ $_name }}</option>
                                        @endforeach
                                    </select>
                                    <div><span id="form-store_id-error" class="error"></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 mb-1">
                            <div class="row align-items-center">
                                <label class="col-md-4" for="form-agency_id">Đại lý:</label>
                                <div class="col-12 col-md-8">
                                    <select id="form-agency_id" name="agency_id"
                                            class="form-control has-select2 control-order-setup-area"
                                            aria-describedby="form-agency_id-error"
                                            required
                                        {{ $default_values['order_type'] == \App\Models\StoreOrder::ORDER_TYPE_DON_TTKEY ? 'disabled' : '' }}>
                                        <option value="">- Đại lý -</option>
                                        @foreach( $formOptions['agencies'] as $_id => $_name )
                                            <option
                                                value="{{ $_id }}" {{ $default_values['agency_id'] == $_id ? 'selected' : '' }}>
                                                {{ $_name }}</option>
                                        @endforeach
                                    </select>
                                    <div><span id="form-agency_id-error" class="error"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(!$formOptions['is_tdv'])
                        <div class="row">
                            <div class="col-12 col-md-6 mb-1">
                                <div class="row align-items-center">
                                    <label class="col-md-4" for="form-product_type">Loại hàng:</label>
                                    <div class="col-12 col-md-8">
                                        <select id="form-product_type" name="product_type"
                                                class="form-control form-select">
                                            @foreach($formOptions['product_types'] as $_id => $_name)
                                                <option value="{{ $_id }}"
                                                    {{ $default_values['product_type'] == $_id ? 'selected' : '' }}>
                                                    {{ $_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-12 col-md-6 mb-1">
                            <div class="row align-items-center">
                                <label class="col-md-4" for="form-order_type">Loại đơn:</label>
                                <div class="col-12 col-md-8">
                                    <select id="form-order_type" name="order_type" class="form-control form-select">
                                        @foreach($formOptions['order_types'] as $_id => $_name)
                                            <option value="{{ $_id }}"
                                                {{ $default_values['order_type'] == $_id ? 'selected' : '' }}>
                                                {{ $_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="row align-items-center">
                                <label class="col-md-4" for="form-order_logistic">Đơn vị ship:</label>
                                <div class="col-12 col-md-8">
                                    <select id="form-order_logistic" name="order_logistic"
                                            class="form-control form-select">
                                        @foreach($formOptions['order_logistics'] as $_id => $_name)
                                            <option value="{{ $_id }}"
                                                {{ $default_values['order_logistic'] == $_id ? 'selected' : '' }}>
                                                {{ $_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 mb-1">
                            <div class="row align-items-center">
                                <label class="col-md-4" for="form-note">Ghi chú:</label>
                                <div class="col-12 col-md-8">
                            <textarea id="form-note" class="form-control" name="note"
                                      rows="2">{{ $default_values['note'] }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="row align-items-center">

                            </div>
                        </div>
                    </div>
                </div>
                <div id="wrapper-order_bonus_info"></div>
                <div id="wrapper-add-product" style="{{ $formOptions['show_product'] ? '' : 'display:none;' }}">
                    <div class="content-header mb-1 mt-2">
                        <h4 class="text-success">Sản phẩm <span class="badge bg-light-warning f2-text">(F2)</span></h4>
                    </div>
                    <div class="mb-2 d-flex flex-wrap align-items-center gap-1">
                        <div class="col-12 col-md-3">
                            <select id="search-product" class="form-control has-select2">
                                @include('pages.store_orders.product-options', ['products'=>$formOptions['products']])
                            </select>
                        </div>
                        <label class="col-6 col-md-1">
                            <input type="number"
                                   id="search-product-qty"
                                   min="0"
                                   max="999"
                                   class="form-control text-end"
                                   value="1">
                        </label>
                        <button id="search-add-product" type="button"
                                class="btn btn-icon btn-primary col col-md-auto">
                            <i data-feather="plus"></i>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table id="product-table" class="table table-bordered">
                            <thead class="table-light"></thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                </div>
                <div class="accordion form-wrapper-promo mt-1"
                     style="{{ ($formOptions['promotion_view'] ?? '') ? '' : 'display:none;' }}">
                    <h4 class="accordion-header d-flex">
                        <button
                            class="text-success accordion-button h4 w-auto ps-0 m-0"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#accordionWrapperPromo"
                            aria-expanded="true"
                            aria-controls="accordionWrapperPromo"
                        >
                            Chương trình khuyến mãi
                        </button>
                        {{--<button class="btn btn-icon promotion-refresh">
                            <i data-feather='refresh-cw'></i>
                        </button>--}}
                    </h4>
                    <div id="accordionWrapperPromo"
                         class="accordion-collapse collapse show">
                        <div class="accordion promotion-wrapper d-flex flex-wrap flex-md-row flex-column">
                            {!! $formOptions['promotion_view'] ?? '' !!}
                        </div>
                    </div>
                </div>
                <div class="text-center mt-2">
                    <button type="button" class="btn btn-success me-1 btn-form-submit">
                        <span class="spinner-border spinner-border-sm hidden" role="status" aria-hidden="true"></span>
                        Tạo đơn
                    </button>

                    <a href="{{ route('admin.store-orders.index') }}" class="btn btn-secondary">
                        <i data-feather='rotate-ccw'></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts-page-vendor')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('vendors/js/forms/cleave/cleave.min.js') }}"></script>
@endpush

@push('scripts-custom')
    <link rel="stylesheet" type="text/css" href="{{ mix('css/pages/store-order.css') }}">
    <script defer>
        window.orderArguments = {
            is_mobile: window.innerWidth < 768,
            route_get_locality: '{{ route('admin.store-orders.get-data-by-locality') }}',
            route_calc_promo: '{{ route('admin.store-orders.get-promotion-items') }}',
            default_products: @json($default_values['products']),
            product_table_thead: ``,
            product_table_tfoot: ``,
        };
        if (window['orderArguments']['is_mobile']) {
            $('.f2-text').addClass('d-none');
            window['orderArguments']['product_table_thead'] = `<tr><th>Sản phẩm</th></tr>`;
            window['orderArguments']['product_table_tfoot'] = `<tr class="hidden row-subtotal ">
                            <td>
                                <div class="d-flex justify-content-between">
                                    <b>Chiết khấu</b>
                                    <b class="row-subtotal-amount"></b>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex justify-content-between">
                                    <b>Tổng số sản phẩm</b>
                                    <b class="row-total-qty"></b>
                                </div>
                            </td>
                        </tr>
                        <tr class="table-light">
                            <td>
                                <div class="d-flex justify-content-between">
                                    <b>Tổng tiền</b>
                                    <b class="row-total-amount"></b>
                                </div>
                            </td>
                        </tr>`;
        } else {
            window['orderArguments']['product_table_thead'] = `<tr>
                            <th class="text-center" style="width: 40px;">STT</th>
                            <th>Sản phẩm</th>
                            <th class="text-end">Số lượng</th>
                            <th class="text-end">Giá</th>
                            <th class="text-end">Thành tiên</th>
                            <th style="width: 40px;" class="text-center">#</th>
                        </tr>`;
            window['orderArguments']['product_table_tfoot'] = `<tr class="hidden row-subtotal ">
                            <td colspan="4"><b>Chiết khấu</b></td>
                            <td class="text-end"><b class="row-subtotal-amount"></b></td>
                            <td class="text-end"></td>
                        </tr>
                        <tr class="row-total table-light">
                            <td colspan="2"><b>Tổng</b></td>
                            <td class="text-end"><b class="pe-1 row-total-qty"></b></td>
                            <td class="text-end"></td>
                            <td class="text-end"><b class="row-total-amount"></b></td>
                            <td class="text-end"></td>
                        </tr>`;
        }
    </script>
    <script defer src="{{ mix('js/core/pages/store_order/add.js') }}"></script>
@endpush
