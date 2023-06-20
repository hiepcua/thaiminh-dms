<?php

use \App\Models\promotion;
use App\Models\Organization;
use App\Models\PromotionCondition;

?>

@extends('layouts.main')
@section('page_title', $page_title)
@section('content')
    <form class="" method="post" action="{{ $formOptions['action'] }}">
        @csrf
        @if(request()->route()->getName() == 'admin.promotion.show')
            @method('PUT')
        @endif
        <h4>Thông tin cơ bản</h4>
        <div class="card">
            <div class="card-body">
                <div class="row mb-1">
                    <div class="col-12">
                        <label class="form-label"
                               for="form-wholesale_price">{{ Promotion::ATTRIBUTES_TEXT['name'] ?? '' }}<span
                                class="text-danger">(*)</span></label>
                        <input type="text" id="form-name" class="form-control" name="name"
                               value="{{ $default_values['name'] }}" placeholder="Tên"/>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-12">
                        <label class="form-label" for="form-code">{{ Promotion::ATTRIBUTES_TEXT['desc'] ?? '' }}</label>
                        <textarea id="form-address" class="form-control" name="desc"
                                  placeholder="Mô tả">{{ $default_values['desc'] }}</textarea>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-auto">
                        <label class="form-label" for="form-code">{{ Promotion::ATTRIBUTES_TEXT['started_at'] ?? '' }}
                            <span class="text-danger">(*)</span></label>
                        <label>
                            @php($disableStartAt = request()->route()->getName() == 'admin.promotion.show'
                                && $default_values['started_at'] <= now()->format('Y-m-d H:m:i')
                            )
                            <input type="text" name="started_at"
                                   style="width: 210px; min-width: 210px;"
                                   @if($disableStartAt) date-endable="{{ $default_values['started_at'] }}" @endif
                                   class="form-control flatpickr-basic flatpickr-input"
                                   placeholder="YYYY-MM-DD" value="{{ $default_values['started_at'] }}"
                                   readonly="readonly">
                        </label>
                    </div>
                    <div class="col-auto">
                        <label class="form-label"
                               for="form-code">{{ Promotion::ATTRIBUTES_TEXT['ended_at'] ?? '' }}</label>
                        <label>
                            <input type="text" name="ended_at"
                                   style="width: 210px; min-width: 210px"
                                   class="form-control flatpickr-basic flatpickr-input"
                                   placeholder="YYYY-MM-DD" value="{{ $default_values['ended_at'] }}"
                                   readonly="readonly">
                        </label>
                    </div>
                </div>
                <div class="row mb-1">
                    <label class="form-label" for="form-code">Khu vực áp dụng<span
                            class="text-danger">(*)</span></label>
                    <div class="col-12">
                        <label style="min-width: 300px; width: 100%">
                            {!!
                                \App\Helpers\Helper::getTreeOrganization(
                                    currentUser: \App\Helpers\Helper::currentUser(),
                                    activeTypes: [
                                        Organization::TYPE_TONG_CONG_TY,
                                        Organization::TYPE_CONG_TY,
                                        Organization::TYPE_MIEN,
                                        Organization::TYPE_KHU_VUC,
                                    ],
                                    excludeTypes: [Organization::TYPE_DIA_BAN],
                                    hasRelationship: true,
                                    setup: [
                                        'multiple' => true,
                                        'name' => 'division_id[]',
                                        'class' => '',
                                        'id' => 'division_id',
                                        'attributes' => '',
                                        'selected' => $default_values['division_id'] ?? [],
                                    ]
                                )
                            !!}
                        </label>
                    </div>
                </div>
                <div class="row mb-1">
                    <label class="form-label" for="form-code">Khu vực loại trừ</label>
                    <div class="col-12">
                        <label style="min-width: 300px; width: 100%">
                            {!!
                                \App\Helpers\Helper::getTreeOrganization(
                                    currentUser: \App\Helpers\Helper::currentUser(),
                                    activeTypes: [
                                        Organization::TYPE_KHU_VUC,
                                    ],
                                    excludeTypes: [Organization::TYPE_DIA_BAN],
                                    hasRelationship: true,
                                    setup: [
                                        'multiple' => true,
                                        'name' => 'division_id_exclude[]',
                                        'class' => '',
                                        'id' => 'division_id_exclude',
                                        'attributes' => '',
                                        'selected' => $default_values['division_id_exclude'] ?? [],
                                    ]
                                )
                            !!}
                        </label>
                    </div>
                </div>
                <div class="row mb-1">
                    <label class="form-label" for="form-code">SL quà tối đa cho 1 cửa hàng</label>
                    <div class="col-12">
                        <input type="number" class="form-control" name="max_gift"
                               value="{{ $default_values['max_gift'] }}"/>
                    </div>
                </div>
                <div class="row mb-1">
                    <label class="form-label">
                        Trạng thái<span class="text-danger">(*)</span>
                    </label>
                    <label class="col-auto">
                        <select name="status" class="form-select">
                            @foreach($formOptions['status'] as $key => $status)
                                <option value="{{ $key }}"
                                        @if($key == $default_values['status'] && $default_values['status'] !== null) selected @endif>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="row mb-1">
                    <label class="form-label">
                        Tự động áp dụng
                        <input type="checkbox" class="form-check-input ms-1" value="{{ Promotion::AUTO_APPLY }}"
                               @if($default_values['auto_apply'] == Promotion::AUTO_APPLY) checked
                               @endif style="width: 18px !important;" name="auto_apply">
                    </label>
                </div>
            </div>
        </div>
        <h4>Khuyến mãi</h4>
        @include('pages.promotion.types-condition')
        <div class="text-center">
            <button type="submit" class="btn btn-success me-1">
                {{ request()->route()->getName() == 'admin.promotion.show' ? 'Cập nhật' : 'Tạo mới' }}
            </button>

            <a href="{{ route('admin.promotion.index') }}" class="btn btn-secondary me-1"><i
                    data-feather='skip-back'></i> Quay lại</a>
        </div>
    </form>
@endsection
@push('scripts-page-vendor')
    <script src="{{ asset('vendors/js/forms/repeater/jquery.repeater.min.js') }}"></script>
@endpush
@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script>
        let products = @json($formOptions['products'] ?? []);
        @php($typeDefault = $default_values['type'] ?? PromotionCondition::TYPE_GIFT_BY_QTY)
        let currentType = 'type{{ $typeDefault }}';
        const TYPE_HAS_MAX_DISCOUNT = '{{ PromotionCondition::TYPE_DISCOUNT_PERCENT }}';
    </script>
    <script src="{{ mix('js/core/pages/promotion/create-or-edit.js') }}"></script>
@endpush

