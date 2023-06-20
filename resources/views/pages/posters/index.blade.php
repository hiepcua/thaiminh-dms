@php
    use \App\Models\Product;
@endphp
@extends('layouts.main')
@push('content-header')
    @can('xem_chuong_trinh_treo_poster')
        <div class="col ms-auto">
            @include('component.btn-add', ['title' => 'Thêm chương trình Poster', 'href' => route('admin.posters.create')])
        </div>
    @endcan
@endpush
@section('content')
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            {!!
                            \App\Helpers\SearchFormHelper::getForm(
                                route('admin.posters.index'),
                                'GET',
                                [
                                    [
                                        "type" => "text",
                                        "id" => "searchInput",
                                        "name" => "search[name]",
                                        "defaultValue" => request('search.name'),
                                        "placeholder" => 'Tên chương trình ',
                                    ],
                                    [
                                        "id" => "select_product",
                                        "type" => "select2",
                                        "name" => "search[product_id]",
                                        "class" => "col-md-3",
                                        "defaultValue" => request('search.product_id', 0),
                                        "options" => $formOptions['option_products'],
                                    ],
                                ]
                            )
                        !!}
                        </div>
                    </div>

                    @include('snippets.messages')
                </div>

                {!! $table->getTable() !!}

            </div>
        </div>
    </div>

@endsection
