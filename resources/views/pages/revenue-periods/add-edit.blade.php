@extends('layouts.main')
@section('page_title', $page_title)
@section('content')
    <div class="card">
        <div class="card-body">
            @if($canEdit)
            <form class="" method="post" action="{{ $formOptions['action'] }}">
            @else
                <div>
            @endif
                @csrf
                @if($rev_period_id)
                    @method('put')
                @endif
                <div class="row mb-1">
                    <div class="col-6">
                        <div class="mb-1">
                            <label class="form-label" for="form-rank">
                                Loại hàng <span class="text-danger">(*)</span>
                            </label>

                            <select selected_product_type="{{$default_values['product_type']}}" id="select_product_type"
                                    class="form-control" required
                                    @if(!$canEdit) disabled @endif
                                    name="product_type">
                                @if(!$default_values['rank_id'])
                                    <option value="">Chọn loại sản phẩm</option>
                                @endif
                                @foreach($productTypes as $_id => $_value)
                                    @if($default_values['rank_id'])
                                        @if($default_values['product_type'] == $_id)
                                            <option @if($default_values['product_type'] == $_id) selected
                                                    @endif value="{{ $_id }}">{{ @$_value['text'] }}</option>
                                        @endif
                                    @else
                                        <option @if($default_values['product_type'] == $_id) selected
                                                @endif value="{{ $_id }}">{{ @$_value['text'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="form-rank">
                                Hạng <span class="text-danger">(*)</span>
                            </label>
                            <select id="form-rank" class="form-control" name="rank_id" required @if(!$canEdit) disabled @endif>
                                @foreach($formOptions['ranks'] as $_rankId => $_rank)
                                    <option value="{{ $_rankId }}"
                                        {{ $_rankId == $default_values['rank_id'] ? 'selected' : '' }}>
                                        {{ $_rank }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="form-periods">
                                Chu kỳ <span class="text-danger">(*)</span>
                            </label>
                            <div class="input-group">
                                <select id="form-period_from" class="form-control input-period" name="period_from"
                                        @if(!$canEdit) disabled @endif required>
                                    <option value="">Chọn chu kỳ bắt đầu</option>
                                    @php $index =0; @endphp
                                    @foreach($periods as $_idProductType => $_periods)
                                        @foreach($_periods as $_i => $_period)
                                            @php $index++; @endphp
                                            <option index="{{ $index }}" product_type_value="{{$_idProductType}}"
                                                    class="option_periods" value="{{ $_period['started_at'] }}"
                                                    data-period="{{ $_i }}"
                                                    data-from-period="{{$_period['started_at']}}"
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
                                <select id="form-period_to" class="form-control input-period" name="period_to" required
                                        @if(!$canEdit) disabled @endif>
                                    <option value="">Chọn đến hết chu kỳ</option>
                                    @php $index =0; @endphp
                                    @foreach($periods as $_idProductType => $_periods)
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
                    </div>
                    <div class="col-6">
                        <div class="mb-1">
                            <label class="form-label" for="form-status">Trạng thái</label>
                            <select id="form-status" class="form-control" name="status" @if(!$canEdit) disabled @endif>
                                @foreach($formOptions['status'] as $v => $n)
                                    <option
                                        value="{{ $v }}" {{ $v == $default_values['status'] ? 'selected' : '' }}>
                                        {{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="form-store_type">
                                Loại nhà thuốc<span class="text-danger">(*)</span>
                            </label>
                            <select id="form-store_type" class="form-control" name="store_type" required @if(!$canEdit) disabled @endif>
                                @foreach($formOptions['store_type'] as $key => $nameStoreType)
                                    <option
                                        value="{{ $key }}" {{ $key == $default_values['store_type'] ? 'selected' : '' }}>
                                        {{ $nameStoreType }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="form-region_apply">
                                Miền áp dụng<span class="text-danger">(*)</span>
                            </label>
                            <select id="form-region_apply" class="form-control" name="region_apply" required @if(!$canEdit) disabled @endif>
                                @foreach($formOptions['region_apply'] as $v => $n)
                                    <option
                                        value="{{ $v }}" {{ $v == $default_values['region_apply'] ? 'selected' : '' }}>
                                        {{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-12 mb-1 mt-2">
                        <table class="table table-bordered table-revenue-period">
                            <thead>
                            <tr>
                                <th rowspan="2" class="align-middle">Group</th>
                                <th rowspan="2" class="align-middle">Nhóm</th>
                                <th rowspan="2" class="align-middle text-end">Doanh số</th>
                                <th rowspan="2" class="align-middle text-end">Tỷ lệ CK (%)</th>
                                <th colspan="3" class="text-center">SP ưu tiên</th>
                                <th rowspan="2" class="align-middle">Tổng CK (%)</th>
                            </tr>
                            <tr>
                                <th class="text-end">Tỷ lệ CK (%)</th>
                                <th class="text-end">SP tối thiểu</th>
                                <th>Số hộp tối thiểu/SP</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($formOptions['product_groups'] as $_parent)
                                @foreach( $_parent->children as $_child )
                                    @if(!isset($inGroups) || in_array($_parent->id,@$inGroups))
                                        <tr class="row_groups" product_type="{{$_parent->product_type}}">
                                            @if( $loop->first )
                                                <td group_id="{{$_parent->id}}"
                                                    rowspan="{{ $_parent->children->count() }}" class="row-group-name">
                                                    {{ $_parent->name }}
                                                </td>
                                            @endif

                                            <td class="row-sub-group-name">
                                                {{ $_child->name }}
                                            </td>

                                            @if( $loop->first )
                                                <td rowspan="{{ $_parent->children->count() }}" class="row-revenue">
                                                    @if(!$canEdit)
                                                        <div class="text-end">
                                                            {{ $default_values['items'][$_parent->id][$_child->id]['revenue'] ?? '' }}
                                                        </div>
                                                    @else
                                                        <input type="text"
                                                               name="items[{{ $_parent->id }}][{{ $_child->id }}][revenue]"
                                                               value="{{ $default_values['items'][$_parent->id][$_child->id]['revenue'] ?? '' }}"
                                                               class="form-control text-end numeral-mask" min="0"
                                                               required>
                                                    @endif
                                                </td>
                                            @endif
                                            <td class="row-discount_rate">
                                                @if(!$canEdit)
                                                    <div class="text-end">
                                                        {{ $default_values['items'][$_parent->id][$_child->id]['discount_rate'] ?? '' }}
                                                    </div>
                                                    <input type="number"
                                                           name="items[{{ $_parent->id }}][{{ $_child->id }}][discount_rate]"
                                                           value="{{ $default_values['items'][$_parent->id][$_child->id]['discount_rate'] ?? '' }}"
                                                           class="form-control text-end input-discount-rate d-none"
                                                           min="0"
                                                           step="0.1">
                                                @else
                                                    <input type="number"
                                                           name="items[{{ $_parent->id }}][{{ $_child->id }}][discount_rate]"
                                                           value="{{ $default_values['items'][$_parent->id][$_child->id]['discount_rate'] ?? '' }}"
                                                           class="form-control text-end input-discount-rate"
                                                           min="0"
                                                           step="0.1"
                                                           required>
                                                @endif

                                            </td>
                                            @if( $loop->first )
                                                <td class="row-priority_discount_rate">
                                                    @if(!$canEdit)
                                                        <div class="text-end">
                                                            {{ $default_values['items'][$_parent->id][$_child->id]['priority_discount_rate'] ?? '' }}
                                                        </div>
                                                        <input type="number"
                                                               name="items[{{ $_parent->id }}][{{ $_child->id }}][priority_discount_rate]"
                                                               value="{{ $default_values['items'][$_parent->id][$_child->id]['priority_discount_rate'] ?? '' }}"
                                                               class="d-none form-control text-end input-discount-rate" min="0"
                                                               step="0.1">
                                                    @else
                                                    <input type="number"
                                                           name="items[{{ $_parent->id }}][{{ $_child->id }}][priority_discount_rate]"
                                                           value="{{ $default_values['items'][$_parent->id][$_child->id]['priority_discount_rate'] ?? '' }}"
                                                           class="form-control text-end input-discount-rate" min="0"
                                                           step="0.1">
                                                    @endif
                                                </td>
                                                <td class="row-priority_product_min">
                                                    @if(!$canEdit)
                                                        <div class="text-end">
                                                            {{ $default_values['items'][$_parent->id][$_child->id]['priority_discount_rate'] ?? '' }}
                                                        </div>
                                                    @else
                                                    <input type="number"
                                                           name="items[{{ $_parent->id }}][{{ $_child->id }}][priority_product_min]"
                                                           value="{{ $default_values['items'][$_parent->id][$_child->id]['priority_product_min'] ?? '' }}"
                                                           class="form-control text-end" min="0">
                                                    @endif
                                                </td>
                                                <td class="wrap-repeater row-product_conditions">
                                                    @if($canEdit)
                                                        <div
                                                            data-repeater-list="items[{{ $_parent->id }}][{{ $_child->id }}][product_conditions]"
                                                        >

                                                            @foreach($default_values['items'][$_parent->id][$_child->id]['product_conditions'] ?? [] as $_i => $_product_conditions )
                                                                <div class="d-flex gap-1 mb-1 align-items-start"
                                                                     data-repeater-item>
                                                                    <div class="repeater-select">
                                                                        <select
                                                                            name="items[{{ $_parent->id }}][{{ $_child->id }}][product_conditions][{{ $_i }}][products]"
                                                                            data-group_id="{{ $_parent->id }}"
                                                                            data-sub_group_id="{{ $_child->id }}"
                                                                            class="select_products form-control has-select2"
                                                                            @if(!$canEdit) disabled @endif
                                                                            multiple>
                                                                            @foreach($formOptions['products'] as $_product)
                                                                                <option value="{{ $_product->id }}"
                                                                                    {{ in_array( $_product->id, $_product_conditions['products']) ? 'selected' : '' }}>
                                                                                    {{ $_product->name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <input type="number"
                                                                           class="form-control text-end repeater-input"
                                                                           name="items[{{ $_parent->id }}][{{ $_child->id }}][product_conditions][{{ $_i }}][qty]"
                                                                           value="{{ $_product_conditions['min_box'] }}">
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <button type="button" class="btn btn-primary btn-sm"
                                                                data-repeater-create>
                                                            <i data-feather="plus"></i> Thêm
                                                        </button>
                                                    @else
                                                        @foreach($default_values['items'][$_parent->id][$_child->id]['product_conditions'] ?? [] as $_i => $_product_conditions )
                                                            <div class="w-100 d-flex" style="margin-bottom: 5px">
                                                                <div class="d-flex gap-1 align-items-start me-3">
                                                                    @foreach($formOptions['products'] as $_product)
                                                                        @if(in_array( $_product->id, $_product_conditions['products']))
                                                                            <span class="badge bg-success">{{ $_product->name }}</span>
                                                                        @endif
                                                                    @endforeach
                                                                </div>

                                                                <span class="ms-auto">{{ $_product_conditions['min_box'] }}</span>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td class="row-total text-end fw-bold">
                                                    <span></span>
                                                </td>
                                            @else
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td class="row-total text-end fw-bold">
                                                    <span></span>
                                                </td>
                                            @endif
                                        </tr>
                                    @endif
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center">
                        @if($rev_period_id)
                            @if($canEdit)
                            <button type="submit" class="btn btn-success me-1">
                                Cập nhập
                            </button>
                            @endif
                        @else
                            <button type="submit" class="btn btn-success me-1">
                                Tạo mới
                            </button>
                        @endif

                        <a href="{{ route('admin.revenue-periods.index') }}" class="btn btn-secondary">
                            <i data-feather='rotate-ccw'></i>
                            Quay lại
                        </a>
                    </div>
                </div>
            </div>
        @if($canEdit)
            </form>
        @else
            <div>
        @endif
    </div>
@endsection
@push('scripts-page-vendor')
    <script src="{{ asset('vendors/js/forms/repeater/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('vendors/js/forms/cleave/cleave.min.js') }}"></script>
@endpush
@push('scripts-custom')
    <script>
        const product_period_url = '{{ route('admin.period.products') }}';
    </script>
    <script defer src="{{ asset('js/core/pages/revenue_period/add-edit.js') }}"></script>
@endpush
