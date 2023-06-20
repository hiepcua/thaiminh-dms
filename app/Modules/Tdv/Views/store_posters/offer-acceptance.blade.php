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
            <div id="form-add-edit-store" class="has-provinces">
                <div class="row mb-1">
                    <div class="col-12 col-md-6 mb-1">
                        <h2>{{$formOptions['store']->name}}</h2>
                        <div>
                        </div>
                        <div>
                            <b>Địa chỉ:</b> {{$formOptions['store']['address']}}
                        </div>
                        <div>
                            <b>Điện thoại:</b> {{$formOptions['store']['phone_web']}}
                        </div>
                    </div>
                </div>
            </div>
            <div id="list-poster" class="has-provinces">
                <h2>{{$formOptions['poster']->name}}</h2>
                <hr>
            </div>
            <div>

                <form class="register-poster" method="post" action="{{$formOptions['action']}}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-1">
                        <div class="col-4">
                            <label class="form-label" for="form-code">Ngày NT đề xuất:</label>
                        </div>
                        <div class="col-8">
                            <input type="text" name="offer_date"
                                   class="form-control flatpickr-basic flatpickr-input"
                                   placeholder="YYYY-MM-DD" value="{{ $default_values['offer_date'] }}"
                                   readonly="readonly">
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-4">
                            <label class="form-label" for="form-code">Lý do:</label>
                        </div>
                        <div class="col-8">
                            <textarea class="form-control" name="offer_reason" rows="2">{{$default_values['offer_reason']}}</textarea>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-success me-1">
                            Lưu
                        </button>
                        <a href="{{ route('admin.tdv.register-poster.list') }}" class="btn btn-secondary me-1"><i
                                data-feather='trash-2'></i> Hủy</a>
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
