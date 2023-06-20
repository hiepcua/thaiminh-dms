@extends('layouts.main')
@section('content')
    <style>
        .image-thumbs .thumb {
            height: 80px;
            object-fit: cover;
        }
    </style>
    <div class="card">
        <div class="card-body">
            <div class="row flex-row-reverse">
                <div class="col">
                    {{--                    {!! \App\Helpers\SearchFormHelper::getForm( route('admin.tdv.register-poster.index', $infoPharmacy->id), 'GET', $formOptions['searchOptions']) !!}--}}

                    {!! $formOptions['searchForm'] !!}
                </div>
            </div>
            @if(isset($infoPharmacy))
                <div id="form-add-edit-store" class="has-provinces">
                    <div class="row mb-1">
                        <div class="col-12 col-md-6 mb-1">
                            <h4>Thông tin nhà thuốc</h4>
                            <div>
                                <b>Tên NT:</b> {{$infoPharmacy['name']}}
                            </div>
                            <div>
                                <b>Địa chỉ:</b> {{$infoPharmacy['address']}}
                            </div>
                            <div>
                                <b>Điện thoại:</b> {{$infoPharmacy['phone_web']}}
                            </div>
                        </div>

                    </div>
                </div>
            @endif
            <div id="list-poster" class="has-provinces">
                <h2>Các chương trình Poster</h2>
                <hr>
                @foreach($allListPoster as $item)

                    <div class="row mb-1">
                        <div class="col-12 col-md-6 mb-1">
                            <div class="row mb-1">
                                <div>
                                    <b>
                                        <h4>{{$item['name']}} <span style="float: right">
                                                @if($item['active'] == 1)

                                                    @if($item['register'] == 0)
                                                        @if(isset($id))
                                                            <a href="{{route('admin.tdv.register-store-poster.create',[ $id, $item['id']])}}"
                                                               class="btn btn-success me-1">Đăng ký</a>
                                                        @else
                                                            <a href="{{route('admin.tdv.register-store-poster.create-by-poster', $item['id'])}}"
                                                               class="btn btn-success me-1">Đăng ký</a>
                                                        @endif
                                                    @else
                                                        <button disabled=""
                                                                class="btn btn-secondary me-1">Đã đăng ký</button>
                                                    @endif
                                                    {{--                                                @else--}}
                                                    {{--                                                    <button disabled=""--}}
                                                    {{--                                                            class="btn btn-secondary me-1">Quá hạn</button>--}}
                                                @endif
                                            </span>
                                        </h4>
                                    </b>
                                </div>
                            </div>
                            <div>
                                <p>
                                    Sản phẩm: {{$item['product_name']}}
                                </p>
                                <p>Ngày đặng ký: {{date('d/m/Y', strtotime($item['start_date']))}}
                                    - {{date('d/m/Y', strtotime($item['end_date']))}}</p>
                                <p>
                                    Số lượng trả thưởng: {{$item['reward_amount']}} sp/{{$item['reward_month']}} tháng
                                </p>
                            </div>
                            <div>
                                <p>{{$item['description']}}</p>
                            </div>
                        </div>
                    </div>
                    <hr>
                @endforeach

                {{--                <div class="text-center">--}}
                {{--                    <a href="{{ route('admin.tdv.store.index') }}" class="btn btn-secondary me-1"><i--}}
                {{--                            data-feather='rotate-ccw'></i> Quay lại</a>--}}
                {{--                    --}}{{--                    <a href="{{ route('admin.tdv.register-poster-index', $id) }}" class="btn btn-success me-1">Đăng ký Poster</a>--}}
                {{--                </div>--}}
            </div>
        </div>
    </div>

    <!-- === START: MODAL === -->
    {{--    @include('pages.stores.modal-search')--}}
    <!-- === END: MODAL === -->
@endsection

@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script>
        const ROUTE_GET_USER_BY_LOCALITY = "{{ route('admin.get-user-by-locality') }}";

        $(document).ready(function () {
            let list_users = (locality_id) => {
                if (locality_id > 0) {
                    ajax(ROUTE_GET_USER_BY_LOCALITY, 'POST', {
                        locality_id: locality_id
                    }).done(function (response) {
                        $('#list-tdv').html(response.htmlString);
                    }).fail(function (error) {
                        console.log(error);
                        alert('Server has an error. Please try again!');
                    });
                }
            };

        });
    </script>
@endpush
