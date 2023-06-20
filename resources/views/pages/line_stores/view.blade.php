@php
    use \App\Models\LineStore;
@endphp
@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12 mb-1">
                    <b>Nhà thuốc: </b>
                    {{ $formOptions['store']->name ?? null }} - {{ $formOptions['store']->code ?? null }}
                </div>

                <div class="col-12 mb-1">
                    <b>Địa bàn: </b>
                    {{ $formOptions['line']->organizations?->name }}
                </div>

                <div class="col-12 mb-1">
                    <b>Tuyến: </b>
                    {{ $formOptions['line']->name ?? null }}
                </div>

                <div class="col-12 mb-1">
                    <b>Số lần thăm/tháng</b>
                    {{ $default_values['number_visit'] ?? null }}
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('admin.line-store-change.index') }}" class="btn btn-secondary me-1 mt-2"><i
                        data-feather='rotate-ccw'></i> Quay lại</a>
            </div>

        </div>
    </div>
@endsection
