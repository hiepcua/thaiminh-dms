@php
    use \App\Models\Store;
    use \App\Models\User;
    $userLocalities     = $formOptions['userLocalities'] ?? [];
    $userProvinces      = $formOptions['userProvinces'] ?? [];
    $userDistricts      = $formOptions['provinceDistricts'] ?? collect();
    $userWards          = $formOptions['provinceWards'] ?? collect();
    $provinceLocalities = $formOptions['provinceLocalities'] ?? collect();
@endphp
@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body">
            <form id="form-add-edit-store" class="has-provinces" method="post" action="{{ $formOptions['action'] }}"
                  enctype="multipart/form-data">
                @csrf
                <h5>Thông tin nhà thuốc</h5>
                <div class="row pt-md-1 pb-1">
                    <div class="col-12 col-md-6 mb-1">
                        <label class="form-label" for="form-type">Loại NT <span class="text-danger">(*)</span></label>
                        <select name="type" id="form-type" class="form-control required" required>
                            @foreach($formOptions['storeType'] as $keyType => $type)
                                <option
                                    value="{{ $keyType }}" {{ $default_values['type'] == $keyType ? 'selected':'' }}>
                                    {{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-1">
                        <label class="form-label" for="form-name">Tên NT <span class="text-danger">(*)</span></label>
                        <input type="text" id="form-name" class="form-control required" name="name"
                               value="{{ $default_values['name'] }}" placeholder="Tên nhà thuốc" required>
                    </div>
                    <div class="col-12 col-md-6 mb-1">
                        <label class="form-label" for="form-code">Mã</label>
                        <input type="text" id="form-code" class="form-control" name="code"
                               value="{{ $default_values['code'] ?? '' }}"
                               placeholder="Mã nhà thuốc" disabled>
                    </div>
                    <div class="col-12 col-md-6 mb-1">
                        <label class="form-label" for="form-province">TP/ Tỉnh <span
                                class="text-danger">(*)</span></label>
                        <select name="province_id" id="form-province"
                                class="form-control has-select2 form-province_id select2 required"
                                required>
                            <option value="">-- Chọn một --</option>
                            @if($formOptions['userProvinces']->count())
                                @foreach ($formOptions['userProvinces'] as $province)
                                    <option value="{{ $province->id ?? '' }}"
                                            {{ $default_values['province_id'] == $province->id ? 'selected':'' }}
                                            data-name="{{ $province->province_name ?? '' }}">
                                        {{ $province->province_name ?? '' }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-1">
                        <label class="form-label" for="form-district">Quận/ Huyện <span
                                class="text-danger">(*)</span></label>
                        <select name="district_id" id="form-district"
                                class="form-control has-select2 form-district_id select2 required" required>
                            <option value="">-- Chọn một --</option>
                            @if($userDistricts->isNotEmpty())
                                @foreach ($userDistricts as $district)
                                    <option value="{{ $district->id }}"
                                            {{ $default_values['district_id'] == $district->id ? 'selected':'' }}
                                            data-name="{{ $district->district_name ?? '' }}">
                                        {{ $district->district_name ?? '' }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-1">
                        <label class="form-label" for="form-ward">Phường/ Xã <span
                                class="text-danger">(*)</span></label>
                        <select name="ward_id" id="form-ward"
                                class="form-control has-select2 form-ward_id select2 required"
                                required>
                            <option value="">-- Chọn một --</option>
                            @if($userWards->isNotEmpty())
                                @foreach ($userWards as $ward)
                                    <option value="{{ $ward->id }}"
                                            {{ $default_values['ward_id'] == $ward->id ? 'selected':'' }}
                                            data-name="{{ $ward->ward_name ?? '' }}">
                                        {{ $ward->ward_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-1">
                        <label class="form-label" for="form-organization_id">Địa bàn <span
                                class="text-danger">(*)</span></label>
                        <select name="organization_id" id="form-organization_id"
                                class="form-control has-select2 select2 required" required>
                            <option value="">-- Chọn một --</option>
                            @if(isset($formOptions['provinceLocalities']))
                                @foreach ($formOptions['provinceLocalities'] as $locality)
                                    <option
                                        value="{{ $locality->id }}"
                                        {{ $locality->id == $default_values['organization_id'] ? 'selected':'' }}>
                                        {{ $locality->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-1">
                        <label class="form-label" for="form-address">Địa chỉ <span
                                class="text-danger">(*)</span></label>
                        <input type="text" name="address" id="form-address" class="form-control required"
                               value="{{ $default_values['address'] }}" required>
                    </div>
                    <div class="col-12 col-md-6 mb-1">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="form-lat">Kinh độ</label>
                                <input type="text" name="lat" id="form-lat" class="form-control"
                                       value="{{ $default_values['lat'] }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="form-lng">Vĩ độ</label>
                                <input type="text" name="lng" id="form-lng" class="form-control"
                                       value="{{ $default_values['lng'] }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 mb-1">
                        <label class="form-label" for="form-phone_owner">SĐT nhận TT <span
                                class="text-danger">(*)</span></label>
                        <input type="number" name="phone_owner" id="form-phone_owner" class="form-control required"
                               value="{{ $default_values['phone_owner'] }}" required>
                    </div>
                    <div class="col-12 col-md-6 mb-1">
                        <label class="form-label" for="form-phone_web">SĐT điểm bán</label>
                        <input type="number" name="phone_web" id="form-phone_web" class="form-control"
                               value="{{ $default_values['phone_web'] }}">
                    </div>

                    <div class="col-12 col-md-6 mb-1">
                        <label class="form-label" for="form-store_status">Trạng thái</label>
                        <select id="form-store_status" class="form-control" name="store_status">
                            @php
                                $default_values['store_status'] = $default_values['store_status'] ?? Store::STATUS_ACTIVE;
                            @endphp

                            @foreach($formOptions['storeStatus'] as $v => $n)
                                <option
                                    value="{{ $v }}" {{ $v == $default_values['store_status'] ? 'selected' : '' }}>
                                    {{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-1">
                        @php $show_web = $default_values['show_web'] ?? Store::SHOW_WEB; @endphp
                        <label class="form-label" for="form-show_web">Hiển thị điểm bán</label>
                        <select id="form-show_web" class="form-control" name="show_web">
                            <option value="1" {{ $show_web == Store::SHOW_WEB ? 'selected' : null }}>Có</option>
                            <option value="0" {{ $show_web !== Store::SHOW_WEB ? 'selected' : null }}>Không</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-1">
                        <label for="formFileMultiple" class="form-label">Hình ảnh</label>
                        <input class="form-control" type="file" id="formFileMultiple" name="image_files[]" multiple
                               accept="image/*">
                        @if (isset($formOptions['files']))
                            <div class="image-thumbs">
                                @foreach ($formOptions['files'] as $item)
                                    <input type="hidden" name="old_files[]" value="{{ $item->source }}">
                                    <x-ZoomImage path="{{ Helper::getImagePath($item->source) }}"
                                                 alt="{{ $item->name }}"/>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-12 mb-1">
                    <div class="row">
                        <div class="col-auto"><h5>Nhà thuốc cha</h5></div>
                        <div class="col-auto">
                            @php
                                $has_parent = $default_values['parent_id'] ?? '';
                            @endphp
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="has_parent" id="form-no_parent"
                                       value="0"
                                    {{ (old('has_parent')=='' || $has_parent=='') ? 'checked' : '' }}>
                                <label class="form-check-label" for="form-no_parent">Không</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="has_parent" id="form-have_parent"
                                       value="1"
                                    {{ old('has_parent') == 1 || $has_parent != '' ? 'checked' : '' }}>
                                <label class="form-check-label" for="form-have_parent">Có</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-1">
                    <div id="box-parent-store"
                         style="{{ $has_parent != '' || old('has_parent') == 1 ? '' : 'display: none' }}">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-12 col-md-6 mb-1">
                                    <input type="hidden" id="form-parent_id" class="form-control" name="parent_id"
                                           value="{{ $default_values['parent_id'] }}">
                                    <div class="input-group input-group-merge">
                                        <input type="text" class="form-control" id="form-parent_name"
                                               name="parent_code_name"
                                               placeholder="Tên nhà thuốc cha"
                                               value="{{ $formOptions['parent_code_name'] ?? '' }}" readonly>
                                        <span class="input-group-text cursor-pointer text-danger">(*)</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <button type="button" id="btn_search_store"
                                            class="btn btn-primary waves-effect waves-float waves-light"
                                            data-bs-toggle="modal" data-bs-target="#searchStore">
                                        <i class="mr-1" data-feather='search'></i> Tìm kiếm NT
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-12">
                        <div class="row">
                            <h5 class="col-auto">TT viết hoá đơn </h5>
                            <div class="col-auto">
                                <input class="form-check-input ms-md-1" type="checkbox"
                                       id="vat_from_parent" name="vat_parent" value="1"
                                    {{ $default_values['parent_id'] && $default_values['vat_parent'] == '1' ? 'checked':'disabled' }}>
                                <label class="form-check-label" for="vat_from_parent">Viết HĐ về thông tin nhà thuốc
                                    cha </label>
                            </div>
                        </div>
                    </div>
                    @php
                        $vatInfoReadOnly = isset($default_values['parent_id']) && $default_values['vat_parent'] == '1' ? 'readonly':'';
                    @endphp
                    <div id="wg_tt_hoadon">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-1">
                                <label class="form-label" for="form-vat_buyer">Người mua hàng</label>
                                <input type="text" name="vat_buyer" id="form-vat_buyer" class="form-control"
                                       value="{{ $default_values['vat_buyer'] }}" {{ $vatInfoReadOnly }}>
                            </div>
                            <div class="col-12 col-md-6 mb-1">
                                <label class="form-label" for="form-vat_company">Tên công ty</label>
                                <input type="text" name="vat_company" id="form-vat_company" class="form-control"
                                       value="{{ $default_values['vat_company'] }}" {{ $vatInfoReadOnly }}>
                            </div>
                            <div class="col-12 col-md-6 mb-1">
                                <label class="form-label" for="form-vat_number">Mã số thuế</label>
                                <input type="text" name="vat_number" id="form-vat_number" class="form-control"
                                       value="{{ $default_values['vat_number'] }}" {{ $vatInfoReadOnly }}>
                            </div>
                            <div class="col-12 col-md-6 mb-1">
                                <label class="form-label" for="form-vat_email">Email</label>
                                <input type="text" name="vat_email" id="form-vat_email" class="form-control"
                                       value="{{ $default_values['vat_email'] }}" {{ $vatInfoReadOnly }}>
                            </div>
                            <div class="col-12 col-md-12">
                                <label class="form-label" for="form-vat_address">Địa chỉ</label>
                                <textarea name="vat_address" id="form-vat_address" class="form-control"
                                          rows="2" {{ $vatInfoReadOnly }}>{{ $default_values['vat_address'] }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <h5>Ghi chú nội bộ</h5>
                <div class="col-12 mb-1">
                    <textarea name="note_private" id="form-note_private" class="form-control"
                              rows="2">{{ $default_values['note_private'] ?? '' }}</textarea>
                </div>
                <h5>Thông tin trình dược viên</h5>
                <div class="col-md-12 mb-2">
                    <div id="list-tdv"></div>
                </div>
                <div class="text-center">
                    <button id="form-btn-save" type="submit" class="btn btn-success me-1">Cập nhật</button>
                    <a href="{{ route('admin.new-stores.index') }}" class="btn btn-secondary me-1"><i
                            data-feather='rotate-ccw'></i> Quay lại</a>
                </div>
            </form>
        </div>
    </div>
    <!-- === START: MODAL === -->
    @include('pages.stores.modal-search')
    <!-- === END: MODAL === -->
@endsection
@push('scripts-custom')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/pages/store.css') }}">
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script>
        const STORE_ID = "{{ $default_values['store_id'] ?? '' }}";
        const ROUTE_GET_LOCALITY_PROVINCE = "{{ route('admin.get-locality-province') }}";
        const ROUTE_GET_USER_BY_LOCALITY = "{{ route('admin.get-user-by-locality') }}";
        const ROUTE_GET_STORE_BY_ID = "{{ route('admin.get-store-by-id') }}";
        const ROUTE_GET_STORE_DUPLICATE = "{{ route('admin.get-store-duplicate') }}";
        const ROUTE_GENERATION_STORE_CODE = "{{ route('admin.generation-store-code') }}";
        const ROUTE_LINE_BY_LOCALITY = "{{ route('admin.get-line-by-locality') }}";
        const IS_EDIT = true;
        const IS_CREATE = false;
        const IS_TDV = false;
    </script>
    <script src="{{ asset('js/core/pages/store/create-or-edit.js') }}"></script>
@endpush
