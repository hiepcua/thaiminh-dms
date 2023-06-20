<?php

use App\Models\Organization;

?>
@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-validate" method="post" action="{{ $formOptions['action'] }}"
                  enctype="multipart/form-data">
                @csrf
                @if($poster_id)
                    @method('put')
                @endif
                <div class="row mb-1">
                    <div class="col-2 offset-2">
                        <label class="form-label" for="form-code">Tên: <span class="text-danger">(*)</span></label>
                    </div>
                    <div class="col-6">
                        <input type="text" id="form-name" class="form-control" name="name"
                               value="{{ $default_values['name'] }}" placeholder="Tên" required>
                    </div>
                </div>

                <div class="row mb-1">
                    <div class="col-2 offset-2">
                        <label class="form-label" for="form-code">Mô tả: </label>
                    </div>
                    <div class="col-6">
                        <textarea name="description" id="form-desc" class="form-control"
                                  rows="2">{{ $default_values['description'] }}</textarea>
                    </div>
                </div>

                <div class="row mb-1">
                    <div class="col-2 offset-2">
                        <label class="form-label" for="form-code">Sản phẩm: <span class="text-danger">(*)</span></label>
                    </div>
                    <div class="col-6">


                        <select id="form-product" class="form-control form-organization_id has-select2"
                                name="product_id" required>
                            <option value="">-- Chọn sản phẩm --</option>
                            @foreach ($formOptions['root_products'] as $item)
                                <option
                                    value="{{ $item->id }}" {{ $item->id==$default_values['product_id'] ? 'selected':'' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-1">
                    <div class="col-2 offset-2">
                        <label class="form-label" for="form-code">Khu vực:<span class="text-danger">(*)</span></label>
                    </div>
                    <div class="col-6">
                        <div class="col-12">
                            <label style="min-width: 300px; width: 100%">
                                {!!
                                    \App\Helpers\Helper::getTreeOrganization(
                                        currentUser: \App\Helpers\Helper::currentUser(),
                                        activeTypes: [
                                            Organization::TYPE_KHU_VUC,
                                            Organization::TYPE_MIEN,
                                            Organization::TYPE_CONG_TY,
                                            Organization::TYPE_TONG_CONG_TY,
                                        ],
                                        excludeTypes: [Organization::TYPE_DIA_BAN],
                                        hasRelationship: true,
                                        setup: [
                                            'multiple' => true,
                                            'name' => 'division_id[]',
                                            'class' => '',
                                            'id' => 'division_id',
                                            'attributes' => 'required',
                                            'selected' => $default_values['division_id'] ?? [],
                                        ]
                                    )
                                !!}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row mb-1">
                    <div class="col-2 offset-2">
                        <label class="form-label" for="form-code">Ngày áp dụng: <span
                                class="text-danger">(*)</span></label>
                    </div>
                    <div class="col-6">

                        <label style="width: 100%">
                            <input type="text" id="fp-range" name="range_date" required
                                   class="form-control flatpickr-range flatpickr-input"
                                   value="{{ $default_values['range_date'] }}"
                                   placeholder="YYYY-MM-DD to YYYY-MM-DD" readonly="readonly">

                        </label>
                    </div>
                </div>


                <div class="row mb-1">
                    <div class="col-2 offset-2">
                        <label class="form-label" for="form-code">Ngày nghiệm thu: <span class="text-danger">(*)</span></label>
                    </div>
                    <div class="col-6">
                        <div class="wrap-repeater-1 aaa">
                            <div data-repeater-list="acceptance_date" id="inputForm">
                                <div class="row mb-1" data-repeater-item>
                                    <div class="col-5">
                                        <label style="width: 100%">
                                            <input type="text" id="fp-range" name="from_to" required
                                                   class="form-control flatpickr-range flatpickr-input"
                                                   value=""
                                                   placeholder="YYYY-MM-DD to YYYY-MM-DD" readonly="readonly">
                                        </label>
                                    </div>
                                    <div class="col-2">
                                        <input data-repeater-delete type="button" class="btn btn-secondary me-1"
                                               value="Xóa"/>
                                    </div>
                                </div>
                            </div>

                            <input data-repeater-create class="btn btn-success me-1" id="add-button" type="button"
                                   value="+ Thêm"/>
                        </div>

                    </div>
                </div>

                <div class="row mb-1">
                    <div class="col-2 offset-2">
                        <label class="form-label" for="form-code">Số lượng trả thưởng: <span
                                class="text-danger">(*)</span></label>
                    </div>
                    <div class="col-6">
                        <div class="row mb-1">
                            <div class="col-4">
                                <label style="width: 100%">
                                    <select id="form-parent_id" class="form-control has-select2" name="reward_month" required>
                                        @foreach ($formOptions['month'] as $key => $item)
                                            <option
                                                value="{{ $key }}" {{ $key==$default_values['reward_month'] ? 'selected':'' }}>{{ $item }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                            <div class="col-8">
                                <label style="width: 100%">
                                    <input type="number" id="form-name" class="form-control" name="reward_amount"
                                           value="{{ $default_values['reward_amount'] }}" placeholder="Số lượng"
                                           required>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-1">
                    <div class="col-2 offset-2">
                        <label class="form-label" for="form-code">Trạng thái: <span
                                class="text-danger">(*)</span></label>
                    </div>
                    <div class="col-6">
                        <div class="row mb-1">
                            <div class="col-4">
                                <label style="width: 100%">
                                    <select class="form-control has-select2" name="status">
                                        @foreach ($formOptions['status'] as $key => $item)
                                            <option
                                                value="{{ $key }}" {{ $key==$default_values['status'] ? 'selected':'' }}>{{ $item }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-2 offset-2">
                        <label class="form-label" for="form-code">Ảnh poster: </label>
                    </div>
                    <div class="col-6">
                        <div class="row mb-1">
                            <div class="col-4">
                                <label style="width: 100%">
                                    <input class="form-control" type="file" name="image">
                                </label>
                            </div>
                            <div class="col-8">
                                <div>
                                    @if(isset($default_values['images']))
                                        <a href="{{ asset(str_replace('public', 'storage', $default_values['images']->source)) }}"
                                           target="_blank">
                                            <img
                                                src="{{ asset(str_replace('public', 'storage', $default_values['images']->source)) }}"
                                                alt="{{ $default_values['images']->name }}"
                                                style="width: 100%">
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <a href="{{ route('admin.posters.index') }}" class="btn btn-secondary me-1"><i
                            data-feather='arrow-left'></i> Quay lại</a>
                    <button type="submit" class="btn btn-success me-1" id="form-btn-save">
                        {{ $poster_id ? 'Cập nhật' : 'Tạo mới' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
@push('scripts-page-vendor')
    <script src="{{ asset('vendors/js/forms/repeater/jquery.repeater.min.js') }}"></script>
@endpush

@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script>
        var $repeater = $('.wrap-repeater-1').repeater(
            {
                show: function () {
                    $('.flatpickr-range').flatpickr({mode: 'range'});
                    $(this).slideDown();
                },
                hide: function (deleteElement) {
                    $(this).slideUp(deleteElement);
                },
                repeaters: [{
                    selector: '.inner-repeater'
                }],
                isFirstItemUndeletable: true
            });

        var acceptance_date = @json($default_values['acceptance_date']);
        if (acceptance_date) {
            $repeater.setList(
                acceptance_date
            );
        }
    </script>
@endpush
