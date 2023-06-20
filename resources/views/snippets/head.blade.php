<meta name="robots" content="noindex,nofollow">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="format-detection" content="telephone=no">
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0,minimal-ui">
<meta name="description" content="CRM - Thái Minh Group">
<meta name="keywords" content="CRM - Thái Minh Group">
<meta name="author" content="Chuyển Đổi Số - Thái Minh Group">
<title>DMS - @yield('page_title', $titlePage ?? 'Trang chủ') - Thái Minh Group</title>

<link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/ico/favicon.ico') }}">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600"
      rel="stylesheet">

<!-- BEGIN: Vendor CSS-->
<link rel="stylesheet" type="text/css" href="{{ asset('vendors/css/vendors.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('vendors/css/pickers/pickadate/pickadate.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('vendors/css/pickers/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('vendors/css/forms/select/select2.min.css') }}">
<!-- END: Vendor CSS-->

<!-- BEGIN: Theme CSS-->
<link rel="stylesheet" type="text/css" href="{{ asset('css/core.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/base/themes/semi-dark-layout.css') }}">
@stack('css-page-vendor')

<!-- BEGIN: Page CSS-->
<link rel="stylesheet" type="text/css" href="{{ asset('css/base/core/menu/menu-types/vertical-menu.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/base/plugins/forms/pickers/form-flat-pickr.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/base/plugins/forms/pickers/form-pickadate.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/base/plugins/forms/form-validation.css') }}">
@stack('css-page-css')
<!-- END: Page CSS-->

<!-- BEGIN: Custom CSS-->
<link rel="stylesheet" type="text/css" href="{{ mix('css/style.css') }}">
<!-- END: Custom CSS-->
