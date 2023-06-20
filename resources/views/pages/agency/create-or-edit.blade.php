<?php

use \App\Models\Agency;
use App\Models\Organization;

?>

@extends('layouts.main')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="" method="post" action="{{ $formOptions['action'] }}">
                @csrf
                @if(request()->route()->getName() == 'admin.agency.show')
                    @method('PUT')
                @endif
                <div class="row mb-1">
                    <div class="col-6">
                        <label class="form-label" for="form-division_id">Khu vực
                            <span class="text-danger">(*)</span></label>
                        {!!
                            \App\Helpers\Helper::getTreeOrganization(
                                currentUser: true,
                                activeTypes: [
                                    Organization::TYPE_KHU_VUC,
                                ],
                                excludeTypes: [
                                    Organization::TYPE_DIA_BAN
                                ],
                                hasRelationship: true,
                                setup: [
                                    'multiple' => true,
                                    'name' => 'division_id[]',
                                    'class' => '',
                                    'id' => 'form-division_id',
                                    'attributes' => 'required',
                                    'selected' => $default_values['division_id']
                                ]
                            )
                        !!}
                    </div>

                    <div class="col-6">
                        <label class="form-label" for="form-wholesale_price">{{ Agency::ATTRIBUTES_TEXT['name'] ?? '' }}
                            <span class="text-danger">(*)</span></label>
                        <input type="text" id="form-name" class="form-control" name="name"
                               value="{{ $default_values['name'] }}" placeholder="Tên" required/>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-6" id="set_up_locality_aria">
                        <label class="form-label" for="form-locality_ids">Địa bàn
                            <span class="text-danger">(*)</span></label>
                        <select id="form-locality_ids" class="form-control form-organization_id has-select2" multiple
                                name="locality_ids[]" required>
                            @foreach($formOptions['locality_ids'] as $locality)
                                <option value="{{ $locality->id }}"
                                        data-parent="{{ $locality->parent_id }}"
                                        class="ajax-locality-option"
                                    {{ in_array($locality->id, $default_values['locality_ids'] ?? []) ? 'selected' : '' }}>
                                    {{ $locality->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="form-code">{{ Agency::ATTRIBUTES_TEXT['address'] ?? '' }}</label>
                        <input type="text" id="form-address" class="form-control" name="address"
                               value="{{ $default_values['address'] }}" placeholder="Địa chỉ">
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-6">
                        <label class="form-label" for="form-code">{{ Agency::ATTRIBUTES_TEXT['code'] ?? '' }}
                            <span class="text-danger">(*)</span></label>
                        <div class="input-group">
                            @if(request()->route()->getName() == 'admin.agency.create')
                                <select class="form-control form-status" name="province_for_code"
                                        id="province_for_code">
                                    <option value="">- Tỉnh thành/Quận huyện -</option>
                                </select>
                            @endif
                            <input type="text" @if(request()->route()->getName() == 'admin.agency.create') id="form-code" @endif class="form-control" name="code"
                                   value="{{ $default_values['code'] }}"
                                   placeholder="Mã đại lý - DL{mã địa bàn}{số tự tăng}"
                                {{ $agencyId ? 'disabled' : '' }}>
                            @if(!$agencyId)
                                <button type="button" class="btn btn-outline-primary btn-reload-code">
                                    <i data-feather='refresh-cw'></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="form-status">{{ Agency::ATTRIBUTES_TEXT['status'] ?? '' }}
                            <span class="text-danger">(*)</span></label>
                        <select id="form-status" class="form-control form-status" name="status">
                            <option value="">- Lựa chọn -</option>
                            @foreach($formOptions['status'] as $key => $status)
                                <option
                                    value="{{ $key }}" {{ $key == $default_values['status'] ? 'selected' : '' }}>
                                    {{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 mt-1">
                        <label class="form-label" for="form-status">
                            Mã số đơn TT<span class="text-danger">(*)</span>
                        </label>
                        <div class="d-flex">
                            <input type="text" id="form-code" class="form-control" name="order_code"
                                   value="{{ $default_values['order_code'] ?? '' }}"
                                   placeholder="Mã số đơn">
                            <select id="form-status" class="form-control form-status ms-1" name="type_tax">
                                <option value="">- Không thuế -</option>
                                @foreach(Agency::TYPE_TAX_TEXTS as $key => $typeTax)
                                    <option
                                        value="{{ $key }}" {{ $key == ($default_values['type_tax'] ?? '') ? 'selected' : '' }}>
                                        {{ $typeTax }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-6 mt-1">
                        <label class="form-label" for="form-tdv_user_id">
                            Trình dược viên làm đại lý
                        </label>
                        <select id="form-tdv_user_id" class="form-control has-select2" name="tdv_user_id">
                            <option value="">- TDV -</option>
                            @foreach($formOptions['tdv_users'] as $tdvUser)
                                <option value="{{ $tdvUser->id }}" class="ajax-tdv-option"
                                    {{ $tdvUser->agency_id == $agencyId ? 'selected' : '' }}>
                                    {{ $tdvUser->username .' - '. $tdvUser->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <br>
                <h4>Thông tin viết hóa đơn</h4>
                <div class="row mb-1 mt-1">
                    <div class="col-6">
                        <label class="form-label" for="form-vat_buyer">{{ Agency::ATTRIBUTES_TEXT['vat_buyer'] ?? '' }}
                        </label>
                        <input type="text" id="form-vat_buyer" class="form-control" name="vat_buyer"
                               value="{{ $default_values['vat_buyer'] }}" placeholder="Hóa đơn - Tên người mua"/>
                    </div>
                    <div class="col-6">
                        <label class="form-label"
                               for="form-vat_company">{{ Agency::ATTRIBUTES_TEXT['vat_company'] ?? '' }}</label>
                        <input type="text" id="form-vat_company" class="form-control" name="vat_company"
                               value="{{ $default_values['vat_company'] }}" placeholder="Hóa đơn - Tên công ty"/>
                    </div>
                </div>
                <div class="row mb-1 mt-1">
                    <div class="col-6">
                        <label class="form-label" for="form-vat_buyer">{{ Agency::ATTRIBUTES_TEXT['vat_number'] ?? '' }}
                        </label>
                        <input type="text" id="form-vat_number" class="form-control" name="vat_number"
                               value="{{ $default_values['vat_number'] }}" placeholder="Hóa đơn - Mã số thuế"/>
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="form-vat_email">Email</label>
                        <input type="email" id="form-vat_email" class="form-control" name="vat_email"
                               value="{{ $default_values['vat_email'] }}" placeholder="Hóa đơn - Email"/>
                    </div>
                </div>
                <div class="row mb-1 mt-1">
                    <div class="col-6">
                        <label class="form-label"
                               for="form-vat_address">{{ Agency::ATTRIBUTES_TEXT['vat_address'] ?? '' }}</label>
                        <input type="text" id="form-vat_address" class="form-control" name="vat_address"
                               value="{{ $default_values['vat_address'] }}" placeholder="Hóa đơn - Địa chỉ"/>
                    </div>
                </div>

                <br>
                <h4>Thông tin thanh toán</h4>
                <div class="row mb-1 mt-1">
                    <div class="col-6">
                        <label class="form-label" for="form-vat_buyer">
                            Thông tin tài khoản<span class="text-danger">(*)</span>
                        </label>
                        <input type="text" id="form-pay_number" class="form-control" name="pay_number"
                               value="{{ $default_values['pay_number'] ?? '' }}" placeholder="Số tài khoản"/>
                    </div>
                    <div class="col-6">
                        <label class="form-label"
                               for="form-pay_service_cost">Phí dịch vụ<span class="text-danger">(*)</span></label>
                        <input type="number" id="form-pay_service_cost" class="form-control" name="pay_service_cost" max="100" min="0"
                               value="{{   \App\Helpers\Helper::formatPrice($default_values['pay_service_cost']) }}" placeholder="Phí dịch vụ (%)"/>
                    </div>
                </div>
                <div class="row mb-1 mt-1">
                    <div class="col-12">
                        <label class="form-label me-1" for="form-vat_personal_tax">Thuế TNCN</label>
                        <input class="form-check-input"
                               type="checkbox"
                               id="form-vat_personal_tax"
                               name="pay_personal_tax"
                               value="1"
                               @if($default_values['pay_personal_tax']) checked @endif>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success me-1">
                        {{ request()->route()->getName() == 'admin.agency.show' ? 'Cập nhật' : 'Tạo mới' }}
                    </button>

                    <a href="{{ route('admin.agency.index') }}" class="btn btn-secondary me-1"><i
                            data-feather='skip-back'></i> Quay lại</a>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts-custom')
    <script>
        const ROUTE_GET_CODE = "{{ route('admin.get-agency-code') }}";
        const ROUTE_GET_PROVINCE = "{{ route('admin.get-province') }}";
    </script>
    <script src="{{ mix('js/core/pages/agency/create-or-edit.js') }}"></script>
@endpush
