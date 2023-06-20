@extends('layouts.main')
@section('content')
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            {!! \App\Helpers\SearchFormHelper::getForm( route('admin.line-store-change.index'), 'GET', $indexOptions['searchOptions'] ) !!}
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
        window.organization.ELE_DIVISION = "#division_id";
        window.organization.ELE_LOCALITY = "#form-locality_id";
        window.organization.ELE_USER = "#form-user_id";
        window.organization.divisionChange();
        window.organization.localityChange();
    </script>
@endpush
