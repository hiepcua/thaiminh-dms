<?php
    use App\Models\Organization;
?>
@extends('layouts.main')
@section('page_title', $page_title)
@push('content-header')
    @can('them_qua_tang')
        <div class="col ms-auto">
            @include('component.btn-add', ['title'=>'Thêm mới', 'href'=>route('admin.gift.create')])
        </div>
    @endcan
@endpush
@section('content')
    <section class="app-user-list">
        <div class="row" id="table-striped">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row flex-wrap">
                            <div class="col">
                                {!!
                                    \App\Helpers\SearchFormHelper::getForm(
                                        route('admin.gift.index'),
                                        'GET',
                                        [
                                            [
                                                "type" => "text",
                                                "id" => "searchInput",
                                                "name" => "search[codeOrName]",
                                                "defaultValue" => request('search.codeOrName'),
                                                "placeholder" => 'Mã/Tên quà tặng',
                                            ],
                                        ]
                                    )
                                !!}
                            </div>

                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="tableProducts" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Mã</th>
                                    <th>Tên</th>
                                    <th>Sản phẩm</th>
                                    <th>Giá tiền</th>
                                    <th>Chức năng</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach( $gifts as $gift )
                                <tr>
                                    <td>{{ $gift->code }}</td>
                                    <td>{{ $gift->name }}</td>
                                    <td>{{ $gift->product?->name }}</td>
                                    <td>{!! \App\Helpers\Helper::formatPrice($gift->price)  !!}</td>
                                    <td>
                                        @can('sua_qua_tang')
                                            <a class="btn btn-sm btn-icon"
                                               href="{{ route('admin.gift.show', $gift->id) }}">
                                                <i data-feather="edit" class="font-medium-2 text-body"></i>
                                            </a>
                                        @endcan
                                        @can('xoa_qua_tang')
                                            <button class="btn-delete-gift btn btn-sm btn-icon delete-record waves-effect waves-float waves-light"
                                                type="button"
                                                data-action="{{ route('admin.gift.destroy', $gift->id) }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather text-danger feather-trash font-medium-2 text-body"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 mt-1 d-flex justify-content-center">
                            {{ $gifts->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/core/pages/gift/index.js') }}"></script>
@endpush
