@php
    use \App\Models\LineStore;
@endphp
@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body">
            <form id="form-approve" method="post" action="{{ $formOptions['action'] }}"
                  enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-12 col-md-6 mb-1">
                        <label class="form-label" for="form-type">Nhà thuốc <span class="text-danger">(*)</span></label>
                        <input type="text" id="form-store-name" class="form-control" name="store-name"
                               placeholder="Tên nhà thuốc"
                               value="{{ $formOptions['store']->name ?? null }} - {{ $formOptions['store']->code ?? null }}"
                               readonly>
                        <input type="hidden" id="form-store" class="form-control" name="store_id"
                               value="{{ $default_values['store_id'] ?? null }}">
                    </div>

                    <div class="col-12 col-md-6 mb-1">
                        <label class="form-label" for="form-type">Địa bàn <span class="text-danger">(*)</span></label>
                        <select id="form-locality" class="form-control" name="locality" disabled>
                            <option
                                value="{{ $formOptions['line']->organizations?->id }}">{{ $formOptions['line']->organizations?->name }}</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6 mb-1">
                        <label class="form-label" for="form-type">Tuyến <span class="text-danger">(*)</span></label>
                        <input type="text" id="form-line-name" class="form-control" name="line-name"
                               placeholder="Tên tuyến" value="{{ $formOptions['line']->name ?? null }}" readonly>
                        <input type="hidden" id="form-store" class="form-control" name="line_id"
                               value="{{ $default_values['line_id'] ?? null }}">
                    </div>

                    <div class="col-12 col-md-6 mb-1">
                        <label class="form-label" for="form-type">Số lần thăm/tháng <span class="text-danger">(*)</span></label>
                        <input type="text" id="form-number_visit" class="form-control" name="number_visit"
                               placeholder="Số lần thăm/ tháng" value="{{ $default_values['number_visit'] ?? null }}"
                               readonly>
                    </div>
                </div>

                <div class="d-flex justify-content-center mt-2">
                    @if($default_values['status'] == LineStore::STATUS_PENDING)
                        <input type="hidden" id="form-status" name="status" value="">
                        <button type="button" id="btn-approve" class="btn btn-success me-1 mb-1"><i
                                data-feather='thumbs-up'></i>
                            Duyệt
                        </button>
                        <button type="button" id="btn-not-approve" class="btn btn-danger me-1 mb-1"><i
                                data-feather='thumbs-down'></i> {{ LineStore::STATUS_TEXTS[LineStore::STATUS_NOT_APPROVE] }}
                        </button>
                    @endif

                    <a href="{{ route('admin.line-store-change.index') }}" class="btn btn-secondary me-1 mb-1"><i
                            data-feather='rotate-ccw'></i> Quay lại</a>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts-custom')
    <script>
        let _form_approve = $('#form-approve'),
            _store_status = $('#form-status'),
            _btn_approve = $('#btn-approve'),
            _btn_not_approve = $('#btn-not-approve');

        _btn_approve.on('click', function () {
            _store_status.val('{{ LineStore::STATUS_ACTIVE }}');
            _form_approve.submit();
        });

        _btn_not_approve.on('click', function () {
            _store_status.val('{{ LineStore::STATUS_NOT_APPROVE }}');
            _form_approve.submit();
        });
    </script>
@endpush

