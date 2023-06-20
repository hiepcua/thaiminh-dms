@extends('layouts.main')
@section('content')
    <style>
         .thumb_image {
             max-width: 100%;
        }
         input[type=file] {
             margin-top: 20px;
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
                            <label class="form-label" for="form-code">Tiêu đề <span class="text-danger">(*)</span>:</label>
                        </div>
                        <div class="col-8">
                            <input type="text" class="form-control" name="title"
                                   value="{{$default_values['title']}}" min="0" placeholder="Tiêu đề">
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-4">
                            <label class="form-label" for="form-code">Ghi chú:</label>
                        </div>
                        <div class="col-8">
                            <textarea class="form-control" name="note" rows="2">{{$default_values['note']}}</textarea>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-4">
                            <label class="form-label" for="form-code">Ảnh treo <span class="text-danger">(*)</span>:</label>
                        </div>
                        <div class="col-8">

                            @if (isset($formOptions['imagePoster'][$formOptions['image_poster']]))
                                <div >
                                    @foreach ($formOptions['imagePoster'][$formOptions['image_poster']] as $item)
                                        <a href="{{ asset(str_replace('public', 'storage', $item->source)) }}" target="_blank">
                                            <img src="{{ asset(str_replace('public', 'storage', $item->source)) }}" alt="{{ $item->name }}"
                                                 class="thumb_image">
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                                @if(count($formOptions['imagePoster'][$formOptions['image_poster']]) == 0)
                            <input class="form-control" type="file" name="{{$formOptions['image_poster']}}[]">
                            <input class="form-control" type="file" name="{{$formOptions['image_poster']}}[]">
                                @endif
                        </div>
                    </div>
                    <hr>

                    @if(count($formOptions['imagePoster'][$formOptions['image_poster']]) > 0)
                        <div class="row mb-1">
                            <div class="col-4">
                                <label class="form-label" for="form-code">Ảnh NT <span class="text-danger">(*)</span>:</label>
                            </div>
                            <div class="col-8">
                                <div>
                                    @foreach ($formOptions['imagePoster'][$formOptions['image_acceptance']] as $item)
                                        <a href="{{ asset(str_replace('public', 'storage', $item->source)) }}" target="_blank">
                                            <img src="{{ asset(str_replace('public', 'storage', $item->source)) }}" alt="{{ $item->name }}"
                                                 class="thumb_image">
                                        </a>
                                    @endforeach
                                </div>
                                <input class="form-control" type="file" name="{{$formOptions['image_acceptance']}}[]">
                                <input class="form-control" type="file" name="{{$formOptions['image_acceptance']}}[]">
                            </div>
                        </div>
                    @endif
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
