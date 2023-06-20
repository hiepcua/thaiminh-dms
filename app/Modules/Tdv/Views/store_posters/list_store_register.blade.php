@extends('layouts.main')
@push('content-header')
    <div class="col-12 col-md-auto ms-auto">
        @include('component.btn-add', ['title' => 'Thêm mới', 'href' => route('admin.tdv.reg-poster')])
    </div>
@endpush
@section('content')
    <style>
        .image-thumbs .thumb {
            height: 80px;
            object-fit: cover;
        }

        a.disabled {
            pointer-events: none;
            cursor: default;
        }
    </style>
    <div class="card">
        <div class="card-body">
            <div class="row flex-row-reverse">
                <div class="col">
                    {!! \App\Helpers\SearchFormHelper::getForm( route('admin.tdv.register-poster.list'), 'GET', $formOptions['searchOptions']) !!}
                </div>
            </div>
            <div id="form-add-edit-store" class="has-provinces">
                <div class="row mb-1">
                    <div class="col-12 col-md-6 mb-1">
                        <h2>Danh sách nhà thuốc treo poster</h2>
                    </div>
                </div>

                @foreach($listStoreUsePoster as $store)
                    @if($store['listPoster']->count())

                        <div class="row mb-1">
                            <div class="col-12 col-md-6 mb-1">
                                <div class="row mb-1">
                                    <div>
                                        <b><h4>
                                                <a href="{{route('admin.tdv.register-poster.index', $store['store']['id'])}}">{{$store['store']['code']}}
                                                    - {{$store['store']['name']}}</a></h4></b>
                                    </div>
                                </div>
                                <div>
                                    <p>Địa chỉ: {{$store['store_address']}}</p>
                                    <p>ĐT: {{$store['store']['phone_web']}}</p>

                                </div>
                                <div>
                                    @foreach($store['listPoster'] as $item)
                                        <div style="padding-bottom: 15px">

                                            <div class="poster-title">
                                                <div>
                                                    <b>{{($item->poster->name)}} </b>
                                                    {{--                                                    {{$item->poster->product->name}}--}}
                                                </div>
                                                <div>
                                                    <div class="btn-group me-1 table-bulk-actions">
                                                        <a class=" dropdown-toggle"
                                                           type="button"
                                                           id="dropdownMenuButton"
                                                           data-bs-toggle="dropdown"
                                                           aria-expanded="false">
                                                            ...
                                                        </a>
                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                            <ul>
                                                                @foreach($item['actions'] as $action)

                                                                    <li>
                                                                        <a class="dropdown-item @if(count($item->image_poster)==0)) disabled @endif"
                                                                           href="{{ $action['router'] }}">
                                                                            {{ $action['text'] }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            @foreach($item->poster->accptance_date_list as $z)
                                                {{--                                                <br>--}}
                                                {{--                                                {{$z->acceptance_start_date}}({{strtotime($z->acceptance_start_date)}})--}}
                                                {{--                                                ---}}
                                                {{--                                                {{$z->acceptance_end_date}}--}}
                                                {{--                                                <br>--}}
                                                @php
                                                    if(strtotime(date('Y-m-d'))>= strtotime($z->acceptance_start_date) &&
                                                    strtotime(date('Y-m-d'))<= strtotime($z->acceptance_end_date))
                                                        {
                                                        $check_date = 1;
                                                    break;
                                                        }
                                                    else
                                                        $check_date = 0;
                                                @endphp
                                            @endforeach
                                            {{--                                            <h1>{{$check_date}}</h1>--}}
                                            <div class="">
                                                @if(count($item->image_poster) == 0)
                                                    <button class="btn btn-outline-secondary waves-effect"
                                                            style="padding: 5px"><i data-feather='image'></i>
                                                        <a style="color: #82868b"
                                                           href="{{route('admin.tdv.image-store-poster.create', $item->id)}}">
                                                            Ảnh treo</a>
                                                    </button>
                                                @endif
                                                @if($check_date == 1 && count($item->image_poster) > 0)

                                                    <button
                                                        {{--                                                        @if(count($item->image_poster) == 0)--}}
                                                        {{--                                                            disabled--}}
                                                        {{--                                                        @endif  --}}
                                                        class="btn btn-outline-secondary waves-effect"
                                                        style="padding: 5px"><i data-feather='shopping-bag'></i>
                                                        <a style="color: #82868b"
                                                           href="{{route('admin.tdv.image-acceptance-store.create', $item->id)}}">
                                                            Ảnh NT</a>
                                                    </button>
                                                    <button class="btn btn-outline-secondary waves-effect"
                                                            style="padding: 5px">
                                                        <a style="color: #82868b"
                                                           href="{{route('admin.tdv.offer-acceptance.create', $item->id)}}"><i
                                                                data-feather='tablet'></i> Đề xuất</a>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div>
                                    <p></p>
                                </div>
                            </div>
                        </div>
                        <hr>
                    @endif
                @endforeach
            </div>
            {{--            <div id="list-poster" class="has-provinces">--}}
            {{--                <h2>Các chương trình Poster</h2>--}}
            {{--                <hr>--}}

            {{--            </div>--}}
        </div>
    </div>
    <style>
        .poster-title {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }

        .dropdown-toggle {
            font-size: 25px;
        }
    </style>
@endsection

@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script defer>
        $('#select_product').on('change', function () {
            let productId = $(this).val();
            console.log(productId);
            $('.ajax-poster-product').remove();

            ajax('ajax-poster-product', 'POST', {
                product_id: productId
            }).done((response) => {
                $("#select_poster").html(response.htmlString);
            }).fail((error) => {
                alert('Server has an error. Please try again!');
            });
        });
    </script>
@endpush
