@php
    use \App\Helpers\Helper;
@endphp
    <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/css/vendors.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/core.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/base/themes/semi-dark-layout.css') }}">
    <title>Document</title>
</head>
<body>
<div class="p-5">
    <div class="mt-5 d-flex justify-content-center">
        <a href="{{ route('admin.dashboard.index') }}" class="brand-logo">
            <img src="{{ asset('images/logo/logo.png') }}" alt="{{ config('app.company') }}">
        </a>
    </div>
    <h2 class="text-center text-danger w-100 mt-2">419 | Trang web đã hết hạn</h2>
</div>
</body>
</html>
