@extends('layouts.main')
@section('content')
    <div class="row">
        <div class="col-lg-6 col-12">
            <div class="card">
                <div class="card-body">
                    <form id="add-edit-organization" class="has-provinces" method="post"
                          action="{{ $formOptions['action'] }}">
                        @csrf
                        @if($organization_id)
                            @method('put')
                        @endif
                        <div class="mb-1">
                            <label class="form-label" for="form-type">Loại</label>
                            <select id="form-type" class="form-control" name="type">
                                <option value="0">- Lựa chọn -</option>
                                @foreach($formOptions['types'] as $v => $n)
                                    <option value="{{ $v }}" {{ $v == $default_values['type'] ? 'selected' : '' }}>
                                        {{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="form-parent_id">Cấp cha</label>
                            <select id="form-parent_id" class="form-control has-select2" name="parent_id" required>
                                <option value="0">- Lựa chọn -</option>
                                @foreach($formOptions['parents'] as $item_parent)
                                    @if($item_parent->type != \App\Models\Organization::TYPE_DIA_BAN)
                                    <option
                                        value="{{ $item_parent->id }}"
                                        {{ $item_parent->id == $default_values['parent_id'] ? 'selected' : '' }}
                                        {{ $organization_id == $item_parent->id ? 'disabled' : '' }}
                                        data-type="{{ $item_parent->type }}"
                                    >
                                        {{ $item_parent->name }}
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="form-name">Tên</label>
                            <input type="text" id="form-name"
                                   class="form-control"
                                   name="name"
                                   value="{{ $default_values['name'] }}" required/>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="form-province_id">Tỉnh</label>
                            <select id="form-province_id" class="form-control form-province_id has-select2"
                                    name="province_id">
                                <option value="0">- Lựa chọn -</option>
                                @foreach($formOptions['provinces'] as $item_province)
                                    <option
                                        value="{{ $item_province->id }}" {{ $item_province->id == $default_values['province_id'] ? 'selected' : '' }}>
                                        {{ $item_province->province_name_with_type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="form-districts">Quận</label>
                            <select id="form-districts" class="form-control form-district_id has-select2"
                                    name="districts[]" multiple>
                                @foreach($formOptions['districts'] as $item_district)
                                    <option
                                        value="{{ $item_district->id }}" {{ in_array($item_district->id, $default_values['districts']) ? 'selected' : '' }}>
                                        {{ $item_district->district_name_with_type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="form-status">Trạng thái</label>
                            <select id="form-status" class="form-control" name="status"
                                    data-old="{{ $default_values['status'] }}">
                                @foreach($formOptions['status'] as $v => $n)
                                    <option
                                        value="{{ $v }}" {{ $v === $default_values['status'] ? 'selected' : '' }}>
                                        {{ $n }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <button type="button" id="btn-form-submit" class="btn btn-success me-1">
                                {{ $organization_id ? 'Cập nhật' : 'Tạo mới' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts-page-vendor')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
@endpush

@push('scripts-custom')
    <script>
        const TYPE_KHAC = {{ \App\Models\Organization::TYPE_KHAC }};
    </script>
    <script defer src="{{ asset('js/core/pages/organization/add-edit.js') }}"></script>
@endpush
