@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body">
            <form id="add-edit-user" class="form-validate" method="post" autocomplete="off"
                  action="{{ $formOptions['action'] }}"
                  enctype="multipart/form-data">
                @csrf
                @if($user_id)
                    @method('put')
                @endif
                <div class="row mb-1">
                    <div class="col-6">
                        <label class="form-label" for="basic-icon-default-name-{{ $user_id }}">Họ và tên</label>
                        <input type="text" id="basic-icon-default-name-{{ $user_id }}"
                               class="form-control"
                               name="name"
                               value="{{ $default_values['name'] }}" required/>
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="email-{{ $user_id }}">Email</label>
                        <input type="text" id="email-{{ $user_id }}"
                               class="form-control"
                               name="email"
                               value="{{ $default_values['email'] }}"/>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-6">
                        <label class="form-label" for="username-{{ $user_id }}">Tài khoản</label>
                        <input type="text" id="username-{{ $user_id }}"
                               class="form-control"
                               name="username"
                               value="{{ $default_values['username'] }}" autocomplete="off"
                        />
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="password-{{ $user_id }}">Mật khẩu</label>
                        <input type="password"
                               id="password-{{ $user_id }}"
                               class="form-control"
                               name="password"
                               value=""/>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-6">
                        <label class="form-label" for="basic-icon-default-phone-{{ $user_id }}">Điện thoại</label>
                        <input type="text" id="basic-icon-default-phone-{{ $user_id }}"
                               class="form-control"
                               name="phone"
                               value="{{ $default_values['phone'] }}"/>
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="basic-icon-default-position-{{ $user_id }}">Chức vụ</label>
                        <input type="text" id="basic-icon-default-position-{{ $user_id }}"
                               class="form-control"
                               name="position"
                               value="{{ $default_values['position'] }}"/>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-6">
                        <label class="form-label" for="base_code-{{ $user_id }}">Mã nhân viên</label>
                        <input type="text" id="base_code-{{ $user_id }}"
                               class="form-control"
                               name="base_code"
                               value="{{ $default_values['base_code'] }}"/>
                    </div>
                    <div class="col-6">
                        <label for="formImage" class="form-label">Hình ảnh (< 200 Kb)</label>
                        <input class="form-control" type="file" id="formImage" name="image" accept="image/*">
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-6">
                        <label class="form-label" for="user-roles">Quyền</label>
                        <select id="user-roles" class="form-select" name="role_id" required>
                            @foreach( $formOptions['roles'] as $role )
                                <option
                                    value="{{ $role->id }}" {{ $role->id== $default_values['role_id'] ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="user-{{ $user_id }}-plan">Trạng thái</label>
                        <select id="user-{{ $user_id }}-plan" class="form-select" name="status">
                            @foreach( $formOptions['status'] as $v => $n )
                                <option
                                    value="{{ $v }}" {{ $v === $default_values['status'] ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-6">
                        <div class="wrap_organization warp_show_hide mb-1">
                            <label class="form-label" for="user-organizations">Sơ đồ tổ chức</label>
                            {!! $formOptions['organizations'] !!}
                            <div><span id="form-organizations-error" class="error"></span></div>
                        </div>
                        <div class="wrap_product_group warp_show_hide">
                            <label class="form-label" for="user-{{ $user_id }}-product_groups">Nhóm sản phẩm</label>
                            <select id="user-{{ $user_id }}-product_groups" class="has-select2 form-select" multiple
                                    name="product_groups[]">
                                @foreach( $formOptions['product_groups'] as $_group )
                                    <option
                                        value="{{ $_group->id }}" {{ in_array($_group->id, $default_values['product_groups']) ? 'selected' : '' }}>
                                        {{ $_group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="w-100">&nbsp;</label>
                        <div class="form-check ">
                            <input class="form-check-input" type="checkbox" id="user-{{ $user_id }}-change_password"
                                   name="change_pass"
                                   value="1" {{ $default_values['change_pass'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="user-{{ $user_id }}-change_password">
                                Đổi mật khẩu</label>
                        </div>
                        <div class="form-check mt-1">
                            <input class="form-check-input checkbox-show-option"
                                   type="checkbox" id="user-{{ $user_id }}-has_parent_id"
                                   name="has_parent_id"
                                   value="1" {{ $default_values['has_parent_id']  ? 'checked' : '' }}
                                   data-wrapper=".wrapper-parent_id">
                            <label class="form-check-label" for="user-{{ $user_id }}-has_parent_id">
                                Liên kết tài khoản</label>
                        </div>
                        <label class="w-100 mt-1 wrapper-parent_id"
                               style="{{ !$default_values['has_parent_id'] ? 'display:none;' : '' }}">
                            <select id="user-{{ $user_id }}-parent_id" class="has-select2 form-select"
                                    name="parent_id">
                                @foreach( $formOptions['users'] as $_user )
                                    <option
                                        value="{{ $_user->id }}" {{ $_user->id == $default_values['parent_id'] ? 'selected' : '' }}>
                                        {{ $_user->username .' - ' .  $_user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        @include('pages.users.agency')
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-success me-1">
                        {{ $user_id ? 'Cập nhật' : 'Tạo mới' }}
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary me-1">
                        <i data-feather='rotate-ccw'></i>
                        Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts-custom')
    <script>
        const CAN_CHOOSE_ORGANIZATION = @json($formOptions['role_can_choose']['organization']);
        const CAN_CHOOSE_PRODUCT_GROUP = @json($formOptions['role_can_choose']['product_group']);
        const CAN_CHOOSE_AGENCY = @json($formOptions['role_can_choose']['agency']);
        const ROUTE_GET_AGENCY = '{{ route('admin.get-agency') }}';
    </script>
    <script defer>
        $(window).on('load', function () {
            setTimeout(function () {
                // $('input[data-type="password"]').attr('type', 'password');
            }, 200);
        });
        $(document).ready(function () {
            let _role_ele = $('#user-roles');

            let remove_organization_value = function () {
                $('.wrap_organization select').val([]).trigger('change');
            }

            let role_change_cb = function (loaded = false) {
                let _role_id = parseInt(_role_ele.val());
                $('.warp_show_hide').hide();
                if (!loaded) {
                    remove_organization_value();
                }

                if (CAN_CHOOSE_ORGANIZATION.includes(_role_id)) {
                    $('.wrap_organization').show();
                    if (_role_id === 6) {
                        $('.wrap_organization select option[data-type="4"]').prop('disabled', true);
                        $('.wrap_organization select option[data-type="5"]').show();
                    } else {
                        $('.wrap_organization select option[data-type="4"]').prop('disabled', false);
                        $('.wrap_organization select option[data-type="5"]').hide();
                    }
                    if (!loaded) {
                        remove_organization_value();
                    }
                }
                if (CAN_CHOOSE_PRODUCT_GROUP.includes(_role_id)) {
                    $('.wrap_product_group').show();
                }
                if (CAN_CHOOSE_AGENCY.includes(_role_id)) {
                    $('.wrapper_has_agency').show();
                }
            }
            role_change_cb(true);

            _role_ele.on('change', function () {
                role_change_cb();
            })

            //
            let checkbox_show_option_cb = function (_ele) {
                if (_ele.is(':checked')) {
                    $(_ele.data('wrapper')).show();
                } else {
                    $(_ele.data('wrapper')).hide();
                }
            }
            $('.checkbox-show-option').on('click', function () {
                checkbox_show_option_cb($(this));
            });
            //
            $('#user-organizations').on('change', function () {
                ajax(ROUTE_GET_AGENCY, 'POST', {locality_id: $(this).val()}).done(function (response) {
                    $('#user_agency_id option').remove();
                    $('#user_agency_id').append(response['htmlString']);
                });
            });
        });
    </script>
@endpush
