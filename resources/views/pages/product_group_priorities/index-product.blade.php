@extends('layouts.main')
@section('content')
    <div class="row" id="table-striped">
        <div class="mb-1">
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary me-1"><i
                    data-feather='rotate-ccw'></i> Quay lại</a>
        </div>

        {!! $table->getTable() !!}
    </div>
@endsection
@push('scripts-custom')
    <script>const ROUTE_DELETE_PRODUCT_GROUP_PRIORITIES = "{{ route('admin.product-group-priorities.index') }}";</script>
    <script src="{{ asset('js/core/pages/product_group_priorities/add-edit.js') }}"></script>
@endpush
