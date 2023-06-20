@extends('layouts.main')
{{--@push('content-header')--}}
{{--    @if($isAddNew)--}}
{{--        <div class="col-12 col-md-auto ms-auto">--}}
{{--            @include('component.btn-add', ['title' => 'Thêm mới', 'href' => route('admin.store-orders.create')])--}}
{{--        </div>--}}
{{--    @endif--}}
{{--@endpush--}}
@section('content')
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row flex-row-reverse">
                        <div class="col">
                            {!! \App\Helpers\SearchFormHelper::getForm( route('admin.key-reward-order.index'), 'GET', $indexOptions['searchOptions'],
                            routeExport: 'admin.key-reward-order.export',
                            permissionExport: 'list_key_reward_export_excel') !!}
                        </div>
                    </div>
                    <!-- === END: SEARCH === -->

                    <!-- === START: MESSAGES === -->
                    @include('snippets.messages')
                    <!-- === END: MESSAGES === -->

                    @if(!$table->isEmpty() && $indexOptions['actions'])
                        <div class="btn-group me-1 table-bulk-actions">
                            <button class="btn btn-primary dropdown-toggle"
                                    type="button"
                                    id="dropdownMenuButton"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                Thao tác
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                @foreach($indexOptions['actions'] as $action)
                                    <a class="dropdown-item action-item" data-action="{{ $action['action'] }}">
                                        {{ $action['text'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                {!! $table->getTable() !!}
            </div>
        </div>
    </div>
    @if(!$table->isEmpty() && $indexOptions['actions'])
        <form id="form-action" action="" method="post" class="hidden">
            @csrf
            <textarea name="ids"></textarea>
        </form>
    @endif
@endsection
@push('css-page-vendor')
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/css/tooltipster/tooltipster.bundle.min.css') }}">
    <link rel="stylesheet" type="text/css"
          href="{{ asset('vendors/css/tooltipster/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-noir.min.css') }}">
@endpush
@push('scripts-page-vendor')
    <script src="{{ asset('vendors/js/tooltipster/tooltipster.bundle.min.js') }}"></script>
@endpush
@push('scripts-custom')
    <link rel="stylesheet" type="text/css" href="{{ mix('css/pages/store-order.css') }}">
    <script defer>
        if (window.innerWidth > 768) {
            $('.table-bulk-actions').detach().prependTo('.table-show-option');
        }
        window.organization.ELE_DIVISION = '#division_id';
        window.organization.ELE_LOCALITY = '#form-locality_id';
        window.organization.ELE_USER = '#form-created_by';
        window.organization.divisionChange();
        window.organization.localityChange();

        tmp_theme.table_check_all($('.table-striped'));

        let tooltipsterOptions = {
            contentAsHTML: true,
            interactive: true,
            theme: 'tooltipster-noir',
        };
        if (window.innerWidth < 769) {
            tooltipsterOptions['trigger'] = 'click';
        }

        $('.tooltipster-store').tooltipster(tooltipsterOptions);
    </script>
    @if($indexOptions['actions'] ?? [])
        <script defer>
            $('.action-item').on('click', function () {
                let _action = $(this).data('action')
                    , _ids = []
                    , _form = $('#form-action');
                $('.row-check:is(:checked)').each((i, ele) => {
                    _ids.push(ele.value)
                });
                if (_ids.length) {
                    _form.attr('action', _action);
                    _form.find('[name="ids"]').val(_ids.join(','));
                    _form.submit();
                } else {
                    alert('Không có đơn nào được chọn.');
                }
            });
        </script>
    @endif
    <script src="{{ asset('vendors/js/moment/moment.min.js') }}"></script>
    <script src="{{ asset('vendors/js/datepickerV2/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('vendors/js/moment/moment.min.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/css/datepickerV2/daterangepicker.css') }}"/>
    <script>

        $('.daterangepicker_v2').daterangepicker({
                                                     ranges: {
                                                         'Today': [moment(), moment()],
                                                         'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                                                         'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                                                         'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                                                         'This Month': [moment().startOf('month'), moment().endOf('month')],
                                                         'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                                                     },
                                                     "showCustomRangeLabel": false,
                                                     "alwaysShowCalendars": true,
                                                     // "startDate": moment().startOf('month'),
                                                     // "endDate": moment().endOf('month'),
                                                     locale: {
                                                         "format": "YYYY-MM-DD",
                                                         "separator": " to ",
                                                     }
                                                 },
                                                 function (start, end, label) {
                                                     console.log('New date range selected: ' + start.format('DD/MM/YYYY') + ' to ' + end.format('DD/MM/YYYY') + ' (predefined range: ' + label + ')');
                                                 });
    </script>
@endpush
