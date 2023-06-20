<!DOCTYPE html>
<html class="loading semi-dark-layout" lang="vi" data-textdirection="ltr">

<head>
    @include('snippets.head')
</head>
<body
    class="vertical-layout vertical-menu-modern navbar-floating footer-static menu-{{ $_COOKIE['menu_status'] ?? 'collapsed not-cookie' }}"
    data-open="click" data-menu="vertical-menu-modern" data-col="" data-framework="laravel">

@include('snippets.menu')
<div class="app-content app-content-custom content">
    <div class="content-wrapper p-0">
        @include('snippets.messages')


        <div class="content-header row">
            @if(isset($titlePage))
                <div class="content-header-left col-md-9 col-12 mb-1 mb-md-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h3 class="content-header-title @if(Breadcrumbs::exists('main')) float-start  @endif mb-0">{{$titlePage}}</h3>
                            <div class="breadcrumb-wrapper">
                                @if(Breadcrumbs::exists('main'))
                                    {{ Breadcrumbs::render('main')}}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @stack('content-header')
        </div>

        @yield('content')
    </div>
</div>

@stack('modal-custom')

<div class="sidenav-overlay"></div>
<div class="drag-target"></div>

@include('snippets.footer')
@include('snippets.scripts-default')
@stack('scripts-custom')
<script>
    $(document).ready(function () {
        @if(request()->session()->has('successMessage') != '')
        Swal.fire({
            position: 'center',
            icon: 'success',
            title: "{{ request()->session()->get('successMessage') }}",
            showConfirmButton: false,
            timer: 3000
        })
        @endif

        @if(request()->session()->has('errorMessage') != '')
        Swal.fire({
            position: 'center',
            icon: 'error',
            title: "{{ request()->session()->get('errorMessage') }}",
            showConfirmButton: false,
            timer: 3000
        })
        @endif
    })
</script>
</body>

</html>
