<?php

use \App\Models\Agency;
use App\Models\Organization;
?>

@extends('layouts.main')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="" method="post" action="{{ $formOptions['action'] }}">
                @csrf
                @if(request()->route()->getName() == 'admin.checkin.show-forget-checkout')
                    @method('PUT')
                @endif

                <div class="row mt-2">
                    <div class="col-md-6">
                        <div class="w-100 mb-1">
                            <label for="title">
                                Trình duyệt viên<span class="text-danger">(*)</span>
                            </label>
                            <select class='form-control has-select2 form-select' name='tdv_id'>
                                <option value="">- Trình duyệt viên -</option>
                                @foreach($formOptions['tdvs'] ?? [] as $tdvId => $tdvName)
                                    <option value="{{ $tdvId }}" @if($tdvId == $default_values['tdv_id']) selected @endif>{{ $tdvName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-100 mb-1">
                            <label for="title">
                                Checkin<span class="text-danger">(*)</span>
                            </label>
                            <input type="text" class="form-control flatpickr-input flatpickr-date-time active"
                               value="{{ $default_values['checkin_at'] ?? '' }}" name="checkin_at" placeholder="YYYY-MM-DD HH:MM" readonly="readonly">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="w-100 mb-1">
                            <label for="title">
                                Nhà thuốc<span class="text-danger">(*)</span>
                            </label>
                            <select class='form-control has-select2 form-select' name='store_id'>
                                <option value="">- Nhà thuốc -</option>
                                @foreach($formOptions['stores'] ?? [] as $storeId => $storeName)
                                    <option value="{{ $storeId }}" @if($storeId == $default_values['store_id']) selected @endif>{{ $storeName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-100 mb-1">
                            <label for="title">
                                Checkout<span class="text-danger">(*)</span>
                            </label>
                            <input value="{{ $default_values['checkout_at'] ?? '' }}"type="text"
                               class="form-control flatpickr-input flatpickr-date-time active" name="checkout_at" placeholder="YYYY-MM-DD HH:MM" readonly="readonly">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="w-100 mb-1">
                            <label for="title">
                                Ghi chú<span class="text-danger">(*)</span>
                            </label>
                            <textarea class="form-control" name="reviewer_note"></textarea>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success me-1">
                        {{ request()->route()->getName() == 'admin.checkin.show-forget-checkout' ? 'Cập nhật' : 'Tạo mới' }}
                    </button>

                    <a href="{{ route('admin.checkin.histories') }}" class="btn btn-secondary me-1"><i
                            data-feather='skip-back'></i> Quay lại</a>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts-custom')
    <script>
        const ROUTE_GET_CODE = "{{ route('admin.get-agency-code') }}";
        const ROUTE_GET_PROVINCE = "{{ route('admin.get-province') }}";
    </script>
    <script src="{{ mix('js/core/pages/agency/create-or-edit.js') }}"></script>
@endpush
