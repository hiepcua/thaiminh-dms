@php
    use \App\Models\Product;
       use App\Models\Organization;
@endphp
@extends('layouts.main')
@section('content')
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-11">
                            {!!
                            \App\Helpers\SearchFormHelper::getForm(
                                route('admin.posters-agency.index'),
                                'GET',
                                [
                                    [
                                        "type" => "text",
                                        "id" => "searchInput",
                                        "name" => "search[pharmacy_name]",
                                        "defaultValue" => request('search.pharmacy_name'),
                                        "placeholder" => 'Mã/Tên NT',
                                    ],
                                    [
                                        "id" => "select_poster",
                                        "type" => "select2",
                                        "name" => "search[poster_id]",
                                        "class" => "col-md-2",
                                        "defaultValue" => request('search.poster_id', -1),
                                        "options" => $formOptions['listPosters'],
                                    ],
                                    [
                                        "id" => "select_status",
                                        "type" => "select2",
                                        "name" => "search[type]",
                                        "class" => "col-md-1",
                                        "defaultValue" => request('search.type', -1),
                                        "options" => $formOptions['types'],
                                    ],
                                    [
                                        "id" => "select_status",
                                        "type" => "select2",
                                        "name" => "search[status]",
                                        "class" => "col-md-1",
                                        "defaultValue" => request('search.status', -1),
                                        "options" => $formOptions['status'],
                                    ],
                                     [
                                    'type'                 => 'divisionPicker',
                                    'divisionPickerConfig' => [
                                        'currentUser'     => true,
                                        'activeTypes'     => [
                                            Organization::TYPE_KHU_VUC,
                                            Organization::TYPE_MIEN,
                                        ],
                                        'excludeTypes'    => [
                                            Organization::TYPE_DIA_BAN,
                                        ],
                                        'hasRelationship' => true,
                                        'setup'           => [
                                            'multiple'   => false,
                                            'name'       => 'search[division_id]',
                                            'class'      => '',
                                            'id'         => 'division_id',
                                            'attributes' => '',
                                            'selected'   =>  request('search.division_id'),
                                            "class" => "col-md-1",
                                        ]
                                    ],
                                ],

                                    [
                                        "id" => "select_tdv",
                                        "type" => "select2",
                                        "name" => "search[tdv_id]",
                                        "class" => "col-md-2",
                                        "defaultValue" => request('search.tdv_id', -1),
                                        "options" => $formOptions['listTdv'],
                                        'other_options' => ['option_class' => 'ajax-tdv-by-division'],
                                    ],
                                ],

            useExport: request()->user()->can('download_nt_treo_poster'),
            routeExport: 'admin.poster-pharmacy.export',
            permissionExport: 'download_nt_treo_poster'
                            )
                        !!}
                            <a href="{{route('admin.poster-pharmacy.export')}}">zzzzzzzzzzzzzz</a>
                        </div>
                        <div class="col-md-1 ">
                            {{--                            @can('xem_chuong_trinh_treo_poster')--}}
                            {{--                                <div class="ms-1">--}}
                            {{--                                    @include('component.btn-add', ['title' => 'Thêm chương trình Poster', 'href' => route('admin.posters.create')])--}}
                            {{--                                </div>--}}
                            {{--                            @endcan--}}
                        </div>
                    </div>

                    @include('snippets.messages')
                </div>

                {!! $table->getTable() !!}

            </div>
        </div>
    </div>

@endsection

@push('scripts-custom')
    <script defer>
        $('#division_id').on('change', function () {
            console.log(ROUTE_GET_LOCALITY);
            let currentDivisionId = $(this).val();

            $('.ajax-tdv-by-division').remove();

            ajax('ajax-tdv-division', 'POST', {
                division_id: currentDivisionId
            }).done((response) => {
                $("#select_tdv").html(response.htmlString);
            }).fail((error) => {
                console.log(error);
                alert('Server has an error. Please try again!');
            });
        });
    </script>
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
