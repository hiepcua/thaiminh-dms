@extends('layouts.main')
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
        <div class="table-responsive">
            @include('pages.reports.revenue_tdv.table')
        </div>
    </div>
@endsection
@push('scripts-custom')
    <script defer>
        window.organization.ELE_DIVISION = '#division_id';
        window.organization.ELE_LOCALITY = '#form-locality_id';
        window.organization.ELE_USER = '#form-user_id';
        window.organization.divisionChange();
        window.organization.localityChange();
    </script>
@endpush
