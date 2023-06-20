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
            @if(isset($store_data))
                <div id="form-add-edit-store" class="has-provinces">
                    <div class="row mb-1">
                        <div class="col-12 col-md-6 mb-1">
                            <h2>Thông tin nhà thuốc</h2>
                            <div>
                                <b>Tên NT:</b> {{$store_data['name']}}
                            </div>
                            <div>
                                <b>Địa chỉ:</b> {{$store_data['address']}}
                            </div>
                            <div>
                                <b>Điện thoại:</b> {{$store_data['phone_web']}}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div id="list-poster" class="has-provinces">
                <h2>{{$poster_data->name}}</h2>
                <hr>
            </div>
            <div>

                <form class="register-poster" method="post"
                      action="{{$router}}">
                    @csrf
                    {{--                    <input name="store_id"--}}
                    {{--                           value="" disabled>--}}
                    @if(!isset($store_data))

                        <div class="row mb-1">
                            <div class="col-4">
                                <label class="form-label" for="form-code">Nhà thuốc:</label>
                            </div>
                            <div class="col-8">

                                <select id="form-agency_id" name="store_id"
                                        class="form-control has-select2 control-order-setup-area"
                                        aria-describedby="form-agency_id-error"
                                        required>
                                    <option value="">- Nhà thuốc -</option>
                                    @foreach( $option['store'] as $_store )
                                        <option
                                            value="{{ $_store->id }}">
                                            {{ $_store->name }}</option>
                                    @endforeach
                                </select>
                                <div><span id="form-agency_id-error" class="error"></span></div>
                            </div>
                        </div>
                    @endif
                    <div class="row mb-1">
                        <div class="col-4">
                            <label class="form-label" for="form-code">Ngang (cm):</label>
                        </div>
                        <div class="col-8">
                            <input type="number" id="poster-width" class="form-control  poster-info" name="poster_width"
                                   value="" min="0" placeholder="Chiều ngang" required>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-4">
                            <label class="form-label" for="form-code">Cao (cm):</label>
                        </div>
                        <div class="col-8">
                            <input type="number" id="poster-height" class="form-control poster-info"
                                   name="poster_height" min="0"
                                   value="" placeholder="Chiều cao" required>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-4">
                            <label class="form-label" for="form-code">Diện tích(m2):</label>
                        </div>
                        <div class="col-8">
                            <input type="number" id="form-name" class="form-control poster-area" name="poster_area"
                                   value="" placeholder="Diện tích" disabled required>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-4">
                            <label class="form-label" for="form-code">Ghi chú:</label>
                        </div>
                        <div class="col-8">
                            <textarea class="form-control" name="poster_note" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="{{ $back_link }}"
                           class="btn btn-secondary me-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="feather feather-rotate-ccw">
                                <polyline points="1 4 1 10 7 10"></polyline>
                                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                            </svg>
                            Quay lại</a>

                        <button type="submit" class="btn btn-success me-1">
                            Đăng ký
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $(".poster-info").on("input", function () {
                var total = 0;
                var height = isNaN(parseInt($("#poster-height").val())) ? 0 : parseInt($("#poster-height").val());
                var width = isNaN(parseInt($("#poster-width").val())) ? 0 : parseInt($("#poster-width").val());
                total = height * width / 10000;
                $(".poster-area").val(total);
            });
        })
    </script>
@endpush
