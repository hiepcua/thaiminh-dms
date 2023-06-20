@extends('layouts/fullLayoutMaster')

@section('page_title', 'Đổi mật khẩu')

@push('css-page-vendor')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset('css/base/pages/authentication.css') }}">
@endpush

@section('content')
    <div class="auth-wrapper auth-basic px-2">
        <div class="auth-inner my-2">
            <!-- Reset Password basic -->
            <div class="card mb-0">
                <div class="card-body">
                    <a href="javascript:void(0);" class="brand-logo">
                        <img src="{{ asset('app-assets/images/logo/logo.png') }}" alt="{{ config('app.company') }}"/>
                    </a>

                    <h4 class="card-title mb-1">Đổi mật khẩu 🔒</h4>
                    <p class="mb-0 fst-italic">Yêu cầu đặt mật khẩu:</p>
                    <ul class="fst-italic">
                        <li>Tối thiểu 8 ký tự</li>
                        <li>Bao gồm chữ hoa, chữ thường và số</li>
                    </ul>

                    <form class="auth-reset-password-form mt-2" method="POST"
                          action="{{ route('admin.password.reset.update') }}">
                        @csrf

                        <div class="mb-1">
                            <div class="d-flex justify-content-between">
                                <label class="form-label" for="reset-password-new">Mật khẩu mới</label>
                            </div>
                            <div class="input-group input-group-merge form-password-toggle @error('password') is-invalid @enderror">
                                <input type="password"
                                       class="form-control form-control-merge @error('password') is-invalid @enderror"
                                       id="reset-password-new" name="password"
                                       aria-describedby="reset-password-new" tabindex="1" autofocus required/>
                            </div>
                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="mb-1">
                            <div class="d-flex justify-content-between">
                                <label class="form-label" for="reset-password-confirm">Nhập lại mật khẩu mới</label>
                            </div>
                            <div class="input-group input-group-merge form-password-toggle">
                                <input type="password" class="form-control form-control-merge"
                                       id="reset-password-confirm"
                                       name="password_confirmation" autocomplete="new-password"
                                       aria-describedby="reset-password-confirm" tabindex="2"/>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" tabindex="3">Cập nhật</button>
                    </form>
                </div>
            </div>
            <!-- /Reset Password basic -->
        </div>
    </div>
@endsection
