@extends('layouts.main')
@push('table-option-start')
    <div class="note-store">
        <span style="background: orange; color: #000">NT Cha</span>
        <span style="background: #ffdd9f; color: #000">NT Con</span>
        <span>NT Lẻ</span>
    </div>
@endpush

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row flex-row-reverse">
                <div class="col">
                    {!! $indexOptions['searchForm'] !!}
                </div>
            </div>
            @include('snippets.messages')
        </div>
        @if($search)
            {!! $table ? $table->getTable() : '' !!}
        @endif
    </div>
    <style>
        .note-store span {
            border: 1px solid #f3f2f7;
            padding: 3px 10px;
        }
    </style>
@endsection
@push('scripts-custom')
    <script defer>
        const STORE_TYPE_PRODUCT_TYPE = @json($indexOptions['mapStoreTypeProductType']);
        $(document).ready(function () {
            let selectStoreType = $('#select_store_type'),
                selectProductType = $('#select_product_type'),
                selectPeriod = $('#select_period');

            selectStoreType.on('change', function () {
                let _val = $(this).val();

                $('option[value != ""]', selectProductType).hide();
                selectProductType.val('');
                if (_val && _val in STORE_TYPE_PRODUCT_TYPE) {
                    $.each(STORE_TYPE_PRODUCT_TYPE[_val], function (i, v) {
                        $(`option[value="${v}"]`, selectProductType).show();
                    });
                }
            });

            selectProductType.on('change', function () {
                let typeValue = $(this).val();

                $('option[value != ""]', selectPeriod).hide();
                selectPeriod.val('');

                if (typeValue) {
                    let periodValue = $(`option[value="${typeValue}"]`, selectProductType).attr('data-period_of_year');

                    $(`option[data-period_of_year="${periodValue}"]`, selectPeriod).show();
                }
            });

            window.organization.ELE_DIVISION = '#division_id';
            window.organization.ELE_LOCALITY = '#form-locality_id';
            window.organization.divisionChange();
        });
    </script>
@endpush
