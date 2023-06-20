@php
    use \App\Models\ProductGroupPriority;
@endphp
@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body row">
            <div class="col-md-3"></div>
            <form class="has-provinces col-md-6" method="post" action="{{ $formOptions['action'] }}">
                @csrf
                @if($priority_id)
                    @method('put')
                @endif
                <input type="hidden" name="priority_id" value="{{ $priority_id ?? '' }}">


                <div class="mb-1">
                    <label class="form-label" for="form-code">Loại hàng <span class="text-danger">(*)</span></label>
                    <select selected_product_type="{{$default_values['product_type']}}" id="select_product_type"
                            class="form-control" required
                            name="product_type">
                        <option value="">Chọn loại sản phẩm</option>
                        @foreach($productTypes as $_id => $_value)
                            <option @if($default_values['product_type'] == $_id) selected
                                    @endif value="{{ $_id }}">{{ @$_value['text'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-1">
                    <label class="form-label" for="form-periods">
                        Chu kỳ <span class="text-danger">(*)</span>
                    </label>
                    <div class="input-group">
                        <select id="form-period_from" class="form-control input-period" name="period_from" required>
                            <option value="">Chọn chu kỳ bắt đầu</option>
                            @php $index =0; @endphp
                            @foreach($periods as $_idProductType => $_periods)
                                @foreach($_periods as $_i => $_period)
                                    @php $index++; @endphp
                                    <option index="{{ $index }}" product_type_value="{{$_idProductType}}"
                                            class="option_periods" value="{{ $_period['started_at'] }}"
                                            data-period="{{ $_i }}" data-from-period="{{$_period['started_at']}}"
                                            data-selected-from-period="{{$default_values['period_from']}}"
                                    @if($_period['started_at'] === $default_values['period_from'] && $default_values['product_type'] == $_idProductType )
                                        'selected'
                                    @else
                                        style="display: none;"
                                    @endif>
                                    {{ $_period['name'] }}
                                    {{--                                            {{ $_period['name'] }} | {{$_period['started_at']}} | product type {{$_idProductType}}--}}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                        <span class="input-group-text">đến</span>
                        <select id="form-period_to" class="form-control input-period" name="period_to">
                            <option value="">Mãi mãi</option>
                            @php $index =0; @endphp
                            @foreach($periodsTo as $_idProductType => $_periods)
                                @foreach($_periods as $_i => $_period)
                                    @php $index++; @endphp
                                    <option index="{{ $index }}" product_type_value="{{$_idProductType}}"
                                            class="option_periods" value="{{ $_period['ended_at'] }}"
                                            data-period="{{ $_i }}" data-to-period="{{$_period['ended_at']}}"
                                            data-selected-to-period="{{$default_values['period_to']}}"
                                            {{ $_period['ended_at'] === $default_values['period_to'] ? 'selected' : '' }} style="display: none;">
                                        {{ $_period['name'] }}
                                        {{--                                                {{ $_period['name'] }} | {{$_period['ended_at']}} | product type {{$_idProductType}}--}}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-1">
                    <label class="form-label" for="form-group_id">Nhóm sản phẩm <span
                            class="text-danger">(*)</span></label>
                    <select id="form-group_id" class="form-control has-select2" name="sub_group_id" required>
                        <option value="">-- Chọn một --</option>
                        @foreach ($formOptions['product_groups'] as $item)
                            @if ($item->level==0)
                                <optgroup class="option_groups" product_type_value="{{$item->product_type}}"
                                          label="{{ $item->name }}" style="display: none;">
                                    {{ $item->name }}
                                </optgroup>
                            @else
                                <option class="option_groups" product_type_value="{{$item->product_type}}"
                                        value="{{ $item->id }}" data-level="{{ $item->level }}"
                                        {{ $default_values['sub_group_id'] == $item->id ? 'selected':'' }} style="display: none;">
                                    {{ $item->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="mb-1">
                    <label class="form-label" for="form-code">Sản phẩm <span class="text-danger">(*)</span></label>
                    <select name="product_id" id="form-product" class="form-control has-select2" required>
                        <option value="">-- Chọn sản phẩm --</option>
                        @foreach ($formOptions['products'] as $item)
                            <option
                                value="{{ $item->id }}"
                                {{ ($item->id == $default_values['product_id']) ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-1">
                    <label class="form-label" for="form-store_type">
                        Loại nhà thuốc <span class="text-danger">(*)</span>
                    </label>
                    <select name="store_type" id="form-store_type" class="form-control has-select2" required>
                        <option value="">-- Loại nhà thuốc --</option>
                        @foreach (ProductGroupPriority::STORE_TYPE_TEXTS as $type => $nameType)
                            <option
                                value="{{ $type }}"
                                {{ ($type == $default_values['store_type']) ? 'selected' : '' }}>
                                {{ $nameType }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-1">
                    <label class="form-label" for="form-region_apply">
                        Miền áp dụng <span class="text-danger">(*)</span>
                    </label>
                    <select name="region_apply" id="form-region_apply" class="form-control has-select2" required>
                        <option value="">-- Miền áp dụng --</option>
                        @foreach (ProductGroupPriority::REGION_APPLY_TEXTS as $region => $regionName)
                            <option
                                value="{{ $region }}"
                                {{ ($region == $default_values['region_apply']) ? 'selected' : '' }}>
                                {{ $regionName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-1">
                    <div class="form-check">
                        <label class="form-check-label" for="form-priority">Sản phẩm ưu tiên </label>
                        <input class="form-check-input" type="checkbox" id="form-priority" name="priority"
                               {{ $default_values['priority'] ? 'checked':'' }} value="1">
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success me-1">
                        <i data-feather='save'></i>
                        {{ $priority_id ? 'Cập nhật' : 'Tạo mới' }}
                    </button>

                    @if(request()->route()->getName() == 'admin.product-group-priorities.edit' && $perEdit)
                        <button type="button" class="btn-delete-priority btn btn-danger me-1"
                                data-action="{{ route('admin.product-group-priorities.destroy', $priority_id) }}"><i
                                data-feather='delete'></i>
                            Xóa
                        </button>
                    @endif

                    <a href="{{ $formOptions['backUrl'] }}" class="btn btn-secondary me-1"><i
                            data-feather='rotate-ccw'></i> Quay lại</a>
                </div>
            </form>
            <div class="col-md-3"></div>
        </div>
    </div>
@endsection
{{--@if(request()->route()->getName() == 'admin.product-group-priorities.edit' && $perEdit)--}}
@push('scripts-custom')
    <script>const ROUTE_DELETE_PRODUCT_GROUP_PRIORITIES = "{{ route('admin.product-group-priorities.index') }}";</script>
    <script defer src="{{ asset('js/core/pages/product_group_priorities/add-edit.js') }}"></script>
@endpush
{{--@endif--}}
