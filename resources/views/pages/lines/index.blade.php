@php
    use App\Models\Line;
    use Carbon\Carbon;
@endphp
@extends('layouts.main')
@push('content-header')
    @if($isAddNew)
        <div class="col ms-auto">
            @include('component.btn-add', ['title' => 'Thêm mới', 'href' => route('admin.lines.create')])
        </div>
    @endif
@endpush
@section('content')
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            {!! \App\Helpers\SearchFormHelper::getForm( route('admin.lines.index'), 'GET', $indexOptions['searchOptions'] ) !!}
                        </div>
                    </div>

                    @include('snippets.messages')

                    <div class="nav-tabs-shadow nav-align-top">
                        <ul class="nav nav-tabs" role="tablist">
                            @php($i = 1)
                            @foreach($results as $organizationKey => $organizationInfo)
                                <li class="nav-item">
                                    <button type="button" class="nav-link {{ $i == 1 ? 'active' : '' }}" role="tab" data-bs-toggle="tab"
                                            data-bs-target="#navs-{{ $organizationKey }}" aria-controls="navs-{{ $organizationKey }}">
                                        {{ $organizationInfo['name'] ?? '' }}
                                    </button>
                                </li>
                                @php($i++)
                            @endforeach
                        </ul>
                        <div class="tab-content">
                            @php($j = 1)
                            @foreach($results as $organizationKey => $organizationInfo)
                                <div class="tab-pane fade {{ $j == 1 ? 'show active' : '' }}" id="navs-{{ $organizationKey }}" role="tabpanel">
                                    @foreach($organizationInfo['items'] as $lineKey => $lineInfo)
                                        <?php
                                            asort($lineInfo['day_of_week']);
                                            $dayOfWeekArr = array_unique($lineInfo['day_of_week']);
                                            $dayOfWeek = '';
                                            $classSpan = '';

                                            foreach ($dayOfWeekArr as $day) {
                                                $classSpan = $dayOfWeek != '' ? 'ms-1' : '';

                                                switch ($day) {
                                                    case 1:
                                                        $dayOfWeek .= "<span class='$classSpan badge rounded-pill bg-primary'>Thứ 2</span>";
                                                        break;
                                                    case 2:
                                                        $dayOfWeek .= "<span class='$classSpan badge rounded-pill bg-secondary'>Thứ 3</span>";
                                                        break;
                                                    case 3:
                                                        $dayOfWeek .= "<span class='$classSpan badge rounded-pill bg-success'>Thứ 4</span>";
                                                        break;
                                                    case 4:
                                                        $dayOfWeek .= "<span class='$classSpan badge rounded-pill bg-danger'>Thứ 5</span>";
                                                        break;
                                                    case 5:
                                                        $dayOfWeek .= "<span class='$classSpan badge rounded-pill bg-warning'>Thứ 6</span>";
                                                        break;
                                                    case 6:
                                                        $dayOfWeek .= "<span class='$classSpan badge rounded-pill bg-info'>Thứ 7</span>";
                                                        break;
                                                    default:
                                                        $dayOfWeek .= "<span class='$classSpan badge rounded-pill bg-dark'>Chủ nhật</span>";
                                                        break;
                                                }
                                            }
                                        ?>
                                        <div class="mt-2">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div>
                                                    {!! $dayOfWeek !!} - <b class="text-primary">{{ $lineInfo['name'] ?? '' }}</b>
                                                </div>
                                                <a href="{{ route('admin.lines.edit', $lineKey) }}" data-href="#" class="ms-auto btn btn-success btn-icon float-end  waves-effect waves-float waves-light">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                    <span>Sửa</span>
                                                </a>
                                                @if(!count($lineInfo['stores']))
                                                <button class="btn-delete-line ms-1 btn btn-danger btn-icon float-end  waves-effect waves-float waves-light"
                                                        data-action="{{ route('admin.lines.destroy', $lineKey) }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather text-danger feather-trash font-medium-2 text-body">
                                                        <polyline style="color: white" points="3 6 5 6 21 6"></polyline>
                                                        <path style="color: white" d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                    <span>Xóa</span>
                                                </button>
                                                @endif
                                            </div>
                                            <table class="table table-striped table-bordered mt-1">
                                                <thead>
                                                <tr>
                                                    <th class="text-center" style="width: 200px">TDV</th>
                                                    <th class="text-center">Nhà thuốc</th>
                                                    <th class="text-center" style="width: 150px">Số lần/tháng</th>
                                                    <th class="text-center" style="width: 250px">Ngày ghé thăm gần nhất</th>
                                                    <th class="text-center" style="width: 250px">Ngày nhập hàng gần nhất</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(count($lineInfo['stores']))
                                                    @foreach($lineInfo['items'] as $item)
                                                        @php($k = 1)
                                                        @php($tdvName = $item['name'])
                                                        @php($storeQty = count($item['items']))
                                                        @foreach($item['items'] as $data)
                                                            <tr>
                                                                @if($k==1)
                                                                    <td rowspan="{{ $storeQty }}">{{ $tdvName }}</td>
                                                                @endif
                                                                <td>{{ $data->store_name }} {{ $data->store_id }}</td>
                                                                <td class="text-center @if($data->checkin_qty >= $data->number_visit) text-success @else text-danger @endif">
                                                                    {{ $data->checkin_qty ?? 0 }}/{{ $data->number_visit }}
                                                                </td>
                                                                <td class="text-center">{{ $data->last_checkin ?? '' }}</td>
                                                                <td class="text-center">{{ $data->last_booking_at ?? '' }}</td>
                                                            </tr>
                                                            @php($k++)
                                                        @endforeach
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="5" class="text-center">Không có dữ liệu</td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    @endforeach
                                </div>
                                @php($j++)
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts-custom')
    <script>
        window.organization.ELE_DIVISION = "#division_id";
        window.organization.ELE_LOCALITY = "#form-locality_id";
        window.organization.ELE_USER = "#form-user_id";
        window.organization.divisionChange();
        window.organization.localityChange();

        $(document).ready(function () {
            function changeFormSearchEmptyLine()
            {
                let isSearchEmptyline = $("#form-empty-line").is(':checked');

                if(isSearchEmptyline) {
                    $('#form_search_name').val('');
                    $('#division_id').val('').trigger('change');
                    $('#form-locality_id').val('');
                    $('#form-user_id').val('');
                    $('#form-status').val('');
                    $('#form-year').val('');
                    $('#form-month').val('');
                    $('#form-weekday').val('');
                }
            }

            changeFormSearchEmptyLine();

            $("#form-empty-line").on('change', function () {
                changeFormSearchEmptyLine();
            })

            $('.btn-delete-line').on('click', function () {
                Swal.fire({
                    title: 'Bạn có chắc chắn muốn xóa tuyến?',
                    showDenyButton: true,
                    confirmButtonText: 'Xóa',
                    denyButtonText: 'Không',
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        ajax($(this).attr('data-action'), 'DELETE', null).done(async (response) => {
                            await Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            })

                            window.location.reload();
                        }).fail((error) => {
                            Swal.fire({
                                position: 'center',
                                icon: 'error',
                                title: error.responseJSON.message,
                                showConfirmButton: false,
                                timer: 3000
                            })
                        });
                    }
                })
            })
        })
    </script>
@endpush
