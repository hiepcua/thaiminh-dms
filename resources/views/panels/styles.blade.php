<!-- BEGIN: Vendor CSS-->
<link rel="stylesheet" href="{{ asset(mix('vendors/css/vendors.min.css')) }}"/>

@yield('vendor-style')
<!-- END: Vendor CSS-->

<!-- BEGIN: Theme CSS-->
<link rel="stylesheet" href="{{ asset(mix('css/core.css')) }}"/>

<!-- BEGIN: Page CSS-->
<link rel="stylesheet" href="{{ asset(mix('css/base/core/menu/menu-types/vertical-menu.css')) }}"/>

{{-- Page Styles --}}
@yield('page-style')

<!-- laravel style -->
<link rel="stylesheet" href="{{ asset(mix('css/overrides.css')) }}"/>

<!-- BEGIN: Custom CSS-->

{{-- user custom styles --}}
<link rel="stylesheet" href="{{ asset(mix('css/style.css')) }}"/>
