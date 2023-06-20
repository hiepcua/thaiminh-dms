<?php

use App\Models\Organization;
use App\Models\Store;

?>
@extends('layouts.main')
@section('content')
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row flex-row-reverse">
                        @can('tdv_them_nha_thuoc')
                            <div class="col-1 mb-1">
                                @include('component.btn-add', ['title' => 'Thêm', 'href' => route('admin.tdv.store.create')])
                            </div>
                        @endcan
                        <?php
                        if (isset($indexOptions['searchOptions'])) {
                            $indexOptions["searchOptions"][] = [
                                "type"         => "selection",
                                "name"         => "search[month]",
                                "defaultValue" => $defaultValues['month'] ?? '',
                                "options"      => $formOptions['months'] ?? [],
                                "id"           => "form-month",
                                "class"        => 'col-md-2'
                            ];
                            $indexOptions["searchOptions"][] = [
                                "type"         => "selection",
                                "name"         => "search[year]",
                                "defaultValue" => $defaultValues['year'] ?? '',
                                "options"      => $formOptions['years'] ?? [],
                                "id"           => "form-year",
                                "class"        => 'col-md-1'
                            ];
                            $indexOptions["searchOptions"][] = [
                                "type"         => "hidden",
                                "name"         => "search[number_day_not_order]",
                                "defaultValue" => $defaultValues['number_day_not_order'] ?? '',
                                "id"           => "form-number_day_not_order",
                                "wrapClass"    => "d-none",
                            ];
                            $indexOptions["searchOptions"][] = [
                                "type"         => "hidden",
                                "name"         => "search[not_enough_visit]",
                                "defaultValue" => $defaultValues['not_enough_visit'] ?? '',
                                "id"           => "form-not_enough_visit",
                                "wrapClass"    => "d-none",
                            ];
                        }
                        ?>
                        <div class="col-md-11">
                            {!! \App\Helpers\SearchFormHelper::getForm( route('admin.tdv.store.index'), 'GET', $indexOptions['searchOptions'] ) !!}
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
            let currentDivisionId = $(this).val();

            $('.ajax-locality-option').remove();
            $('.ajax-tdv-option').remove();

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

