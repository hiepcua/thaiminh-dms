<?php
    use App\Models\Organization;
?>
@extends('layouts.main')
@section('page_title', $page_title)
@section('content')
    <section class="app-user-list">
        <div class="row" id="table-striped">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        {!! \App\Helpers\SearchFormHelper::getForm(
                            route('admin.report.agency-orders'),
                            'GET',
                            $indexOptions['searchOptions'],
                            useExport: request()->user()->can('download_don_nhap_dai_ly'),
                            routeExport: 'admin.report.agency-orders.export',
                            permissionExport: 'download_ban_ke_dai_ly'
                        ) !!}
                    </div>

                    {!! $table->getTable() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script>
        const ROUTE_GET_AGENCY = "{{ route('admin.get-agency') }}";

        $('#division_id').on('change', function () {
            let currentDivisionId = $(this).val();

            $('.ajax-locality-option').remove();
            $('.ajax-agency-option').remove();

            ajax(ROUTE_GET_LOCALITY, 'POST', {
                division_id: currentDivisionId
            }).done((response) => {
                $("#form-locality_id").append(response.htmlString);
                $("#form-agency_id").append('<option value="" class="ajax-agency-option" selected="">- Đại lý -</option>');
            }).fail((error) => {
                console.log(error);
                alert('Server has an error. Please try again!');
            });
        });

        $('#form-locality_id').on('change', function () {
            let currentLocalityId = $(this).val();

            $('.ajax-agency-option').remove();

            ajax(ROUTE_GET_AGENCY, 'POST', {
                locality_id: currentLocalityId
            }).done((response) => {
                $("#form-agency_id").append(response.htmlString);
            }).fail((error) => {
                console.log(error);
                alert('Server has an error. Please try again!');
            });
        });
    </script>
@endpush
