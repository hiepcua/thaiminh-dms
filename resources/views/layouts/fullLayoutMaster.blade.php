<!DOCTYPE html>
<html class="loading" lang="vi" data-textdirection="ltr">

<head>
    @include('snippets.head')
</head>


<body
    class="vertical-layout vertical-menu-modern navbar-floating blank-page footer-static menu-{{ $_COOKIE['menu_status'] ?? 'collapsed not-cookie' }}"
    data-menu="vertical-menu-modern" data-col="blank-page" data-framework="laravel" data-asset-path="{{ asset('/')}}">

<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>

    <div class="content-wrapper">
        <div class="content-body">

            {{-- Include Startkit Content --}}
            @yield('content')

        </div>
    </div>
</div>
<!-- End: Content-->

{{-- include default scripts --}}
@include('panels/scripts')

<script type="text/javascript">
    $(window).on('load', function () {
        if (feather) {
            feather.replace({
                width: 14,
                height: 14
            });
        }
    })
</script>

</body>

</html>
