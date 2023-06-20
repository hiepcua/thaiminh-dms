@extends('layouts.main')
@push('content-header')
    @can('them_ql_hop_dong_key')
        <div class="col ms-auto">
            @include('component.btn-add', ['title' => 'Thêm mới', 'href' => route('admin.revenue-periods.create')])
        </div>
    @endcan
@endpush
@section('content')
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-body" style="padding-bottom:12px;">
                    <div class="row flex-row-reverse">

                        <input type="hidden" id="search_product_type" value="{{request('search.product_type', null)}}" />
                        <input type="hidden" id="search_period" value="{{request('search.period', null)}}" />

                            <select  id="form-periods" class="form-control input-period" name="period_from" required style="display: none;">
                                <option value="">Chọn kỳ bắt đầu</option>
                                @foreach($periods as $_idProductType => $_periods)
                                    @foreach($_periods as $_i => $_period)
                                        <option index="{{$loop->index}}" product_type_value="{{$_idProductType}}" class="option_periods" value="{{ $_period['started_at'] }}"
                                                data-period="{{ $_i }}" data-from-period="{{$_period['started_at']}}" data-selected-from-period="{{@$default_values['period_from']}}"
                                        @if($_period['started_at'] == request('search.period', null) && @$default_values['product_type'] == $_idProductType )
                                            'selected' @else @endif>
                                        {{ $_period['name'] }}

{{--                                        {{ $_period['name'] }} | {{$_period['started_at']}} | product type {{$_idProductType}}--}}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        <div class="col">
                        {!!
                            \App\Helpers\SearchFormHelper::getForm(
                                route('admin.revenue-periods.index'),
                                'GET',
                                [
                                    [
                                        "id" => "select_product_type",
                                        "type" => "selection",
                                        "name" => "search[product_type]",
                                        "defaultValue" => request('search.product_type', null),
                                        "options" => $formOptions['product_types'],
                                    ],
                                    [
                                        "type" => "selection",
                                        "name" => "search[rank]",
                                        "defaultValue" => request('search.rank', null),
                                        "options" => $formOptions['ranks'],
                                    ],
                                    [
                                        "type" => "selection",
                                        "name" => "search[store_type]",
                                        "defaultValue" => request('search.store_type', null),
                                        "options" => $formOptions['store_type'],
                                    ],
                                    [
                                        "type" => "selection",
                                        "name" => "search[region_apply]",
                                        "defaultValue" => request('search.region_apply', null),
                                        "options" => $formOptions['region_apply'],
                                    ],
                                ],
                            )
                        !!}
                        </div>
                    </div>
                    <!-- === END: SEARCH === -->

                    @include('snippets.messages')
                </div>

                <div class="table-responsive">
                    <table id="tableProducts" class="table table-bordered table-striped overflow-hidden">
                        <thead>
                        <tr>
                            <th class="text-center">Chu Kỳ</th>
                            <th class="text-center">Loại nhà thuốc</th>
                            <th class="text-center">Miền áp dụng</th>
                            <th class="text-center">Hạng</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Chức năng</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if (!$results->isEmpty())
                            @foreach($results as $_key => $items)
                                <td width="400px" class="text-center" rowspan="{{count($items)+1}}">{!!$_key!!}</td>
                                @foreach($items as $i => $item)
                                <tr>
                                    <td width="60px" class="text-center">{{ \App\Models\RevenuePeriod::STORE_TYPE_TEXTS[$item->store_type] ?? '' }}</td>
                                    <td width="60px" class="text-center">{{ \App\Models\RevenuePeriod::REGION_APPLY_TEXTS[$item->region_apply] ?? '' }}</td>
                                    <td width="60px" class="text-center">{{ $item->rank ? $item->rank->name : 'Empty Rank' }}</td>
                                    <td width="60px" class="text-center">
                                        <span class="badge bg-{{ $item->status==1 ? 'success' : 'secondary' }}">
                                            {{ $item->status_name }}
                                        </span>
                                    </td>
                                    <td width="100px" class="text-center">
                                        <a class="btn btn-sm btn-icon"
                                           href="{{ route('admin.revenue-periods.edit', $item->id) }}">
                                            <i data-feather="edit" class="font-medium-2 text-body"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center">Không tìm thấy dữ liệu phù hợp!</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>

                <!-- === START: Pagination === -->
                <div class="row" style="margin-top:25px;">
                    <div class="col-sm-12 d-flex justify-content-center">
{{--                        {{ !empty($results) ? $results->withQueryString()->links() : '' }}--}}
                    </div>
                </div>
                <!-- === END: Pagination === -->

            </div>
        </div>
    </div>

@endsection

@push('scripts-custom')
<script>
    window.rev_period = {};

    rev_period.input_product_type_change = function () {

        let inputPeriod =  $('#search_period').val();
        $('#select_product_type').on('change', function () {

            var selectBox = $(this);
            var selectBoxSelected = $(this).val();
            var exists_product_type = $("#search_product_type").val();

            let html = '<option value="" class="option_periods"  data-from-period="fromPeriod" >-Chu kỳ-</option>';

            $('#select_periods').html(html);
            $("#form-periods option").each(function () {
                // Process item period
                var product_type = $(this).attr("product_type_value");
                var fromPeriod = $(this).attr("data-from-period");
                var index = $(this).attr("index");

                var periodName = $(this).text();

                if(selectBoxSelected==product_type){
                    let html = '<option value="'+fromPeriod+'" class="option_periods" index="'+index+'" product_type_value="'+product_type+'"  data-from-period="'+fromPeriod+'" >' +periodName+ '</option>';

                    $('#select_periods').append(html);

                    if(inputPeriod===fromPeriod && exists_product_type === selectBoxSelected ) {
                        index++;
                        console.log("p type: "+product_type+ " | select box: "+selectBoxSelected + " | exist: "+exists_product_type );
                        console.log("value: "+inputPeriod+ " | index: "+index + ' | day ' + fromPeriod);
                        $("#select_periods option").eq(index).prop('selected', true);
                    }
                }

                var fromPeriod = $(this).attr("data-from-period");
                var fromPeriodSelected = $(this).attr("data-selected-from-period");
                var toPeriod = $(this).attr("data-to-period");
                var toPeriodSelected = $(this).attr("data-selected-to-period");

            });



        });
    };


    rev_period.init = function () {
        rev_period.input_product_type_change();
    };

    $(document).ready(function () {
        rev_period.init();
        // $('#select_product_type').
        $('#select_product_type').change();
    });

</script>
@endpush
