@extends('layouts/fullLayoutMaster')

@section('page_title', 'Đăng nhập')

@push('css-page-vendor')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset('css/base/pages/authentication.css') }}">
@endpush

@section('content')
    <div class="auth-wrapper auth-basic px-2">
        <div class="auth-inner my-2">
            <!-- Login basic -->
            <div class="card mb-0">
                <div class="card-body">
                    <a href="#" class="brand-logo">
                        <img src="{{ asset('images/logo/logo.png') }}" alt="{{ config('app.company') }}"/>
                        {{--            <h2 class="brand-text text-primary ms-1">{{ config('app.company') }}</h2>--}}
                    </a>

                    <h4 class="card-title mb-1">Chào mừng bạn đến với phần mềm DMS 👋</h4>
                    <p class="card-text mb-2">Vui lòng đăng nhập vào tài khoản của bạn và bắt đầu sử dụng các chức
                        năng.</p>

                    @if (session('status'))
                        <div class="alert alert-success mb-1 rounded-0" role="alert">
                            <div class="alert-body">
                                {{ session('status') }}
                            </div>
                        </div>
                    @endif

                    <form class="auth-login-form mt-2" method="POST" action="{{ route('admin.login') }}">
                        @csrf
                        <div class="mb-1">
                            <label for="login-email" class="form-label">Tài khoản</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                   id="login-email" name="username"
                                   placeholder="userdms" aria-describedby="login-email" tabindex="1" autofocus
                                   value="{{ old('username') }}"/>
                            @error('username')
                            <span class="invalid-feedback" role="alert">
                              <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="mb-1">
                            <div class="d-flex justify-content-between">
                                <label class="form-label" for="login-password">Mật khẩu</label>
                            </div>
                            <div class="input-group input-group-merge form-password-toggle">
                                <input type="password" class="form-control form-control-merge" id="login-password"
                                       name="password"
                                       placeholder="**********"
                                       tabindex="2">
                                <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                            </div>
                        </div>
                        <div class="mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember"
                                       tabindex="3"
                                    {{ old('remember') ? 'checked' : '' }} />
                                <label class="form-check-label" for="remember"> Remember Me </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-info w-100" tabindex="4">Sign in</button>
                    </form>

                    <div class="divider my-2">
                        <div class="divider-text">or</div>
                    </div>

                    <div class="auth-footer-btn d-flex justify-content-center">
                        <a href="{{ route('admin.auth.provider', ['google']) }}" class="btn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="24px" height="24px">
                                <path fill="#fbc02d"
                                      d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12	s5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24s8.955,20,20,20	s20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                                <path fill="#e53935"
                                      d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039	l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                                <path fill="#4caf50"
                                      d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36	c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                                <path fill="#1565c0"
                                      d="M43.611,20.083L43.595,20L42,20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571	c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
                            </svg>
                            Đăng nhập bằng Google
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Login basic -->
        </div>
    </div>
@endsection
