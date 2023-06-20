<?php

use App\Models\Organization;
use App\Models\Store;

?>
@extends('layouts.main')
@push('content-header')
    @if($isAddNew)
        <div class="col ms-auto">
            @include('component.btn-add', ['title' => 'Thêm mới', 'href' => route('admin.stores.create')])
        </div>
    @endif
@endpush
@section('content')
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row flex-row-reverse">

                        <div class="col">
                            {!! \App\Helpers\SearchFormHelper::getForm( route('admin.stores.index'), 'GET', $indexOptions['searchOptions'] ) !!}
                        </div>
                    </div>

                    @include('snippets.messages')

                    {!! $table->getTable() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts-custom')
    <script defer>
        $('#division_id').on('change', function () {
            console.log(11);
            let currentDivisionId = $(this).val();

            $('.ajax-locality-option').remove();

            ajax(ROUTE_GET_LOCALITY, 'POST', {
                division_id: currentDivisionId
            }).done((response) => {
                $("#form-locality_id").html(response.htmlString);
            }).fail((error) => {
                console.log(error);
                alert('Server has an error. Please try again!');
            });
        });
    </script>
@endpush

