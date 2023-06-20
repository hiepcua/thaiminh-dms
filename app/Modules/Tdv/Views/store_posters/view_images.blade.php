@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body">
            <div id="form-add-edit-store" class="has-provinces">
                {{--                <div class="row mb-1">--}}
                {{--                    <div class="col-12 col-md-6 mb-1">--}}
                {{--                        <h2>Danh sách nhà thuốc treo poster</h2>--}}
                {{--                    </div>--}}
                {{--                </div>--}}
                <div class="row mb-1">
                    <div class="col-12 col-md-6 mb-1">
                        <div class="row mb-1">
                            <div>
                                <b><h4>{{$poster_reg->store->name}}</h4></b>
                            </div>
                        </div>
                        <div>
                            <p>Địa chỉ: {{$poster_reg->store->address}}</p>
                            <p>ĐT: {{$poster_reg->store->phone_web}}</p>
                        </div>
                        <div>
                            <b>{{$poster_reg->poster->name}}</b>
                            @if($info_poster)
                                <ul>
                                    <li>
                                        <p>
                                            Người nhập:
                                        </p>
                                    </li>
{{--                                    <li>--}}
{{--                                        <p>--}}
{{--                                            Thời gian nhập: {{$images->first()->created_at ?? ""}}--}}
{{--                                        </p>--}}
{{--                                    </li>--}}
                                    {{--                                <li>--}}
                                    {{--                                    <p>--}}
                                    {{--                                        Trạng thái:--}}
                                    {{--                                    </p>--}}
                                    {{--                                </li>--}}
                                    {{--                                <li>--}}
                                    {{--                                    <p>--}}
                                    {{--                                        Lý do:--}}
                                    {{--                                    </p>--}}
                                    {{--                                </li>--}}
                                    {{--                                <li>--}}
                                    {{--                                    <p>--}}
                                    {{--                                        Người duyệt:--}}
                                    {{--                                    </p>--}}
                                    {{--                                </li>--}}
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row mb-1">
                    @foreach($images as $image)
                        <div class="col-12">
                            <p>
                                Thời gian nhập: {{date('m/d/Y', strtotime($image->created_at)) ?? ""}}
                            </p>
                            <img src="{{ asset(str_replace('public', 'storage', $image->source)) }}"
                                 alt="{{ $image->name }}" style="max-width: 100%"
                                 class="thumb_image">
                        </div>
                        <hr>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="text-center">
            <a href="{{ route('admin.tdv.register-poster.list') }}" class="btn btn-secondary me-1"> Quay lại</a>
        </div>
    </div>
@endsection

@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
@endpush
