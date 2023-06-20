<?php
use \App\Models\gift;
use App\Models\Organization;
?>

@extends('layouts.main')
@section('page_title', $page_title)
@section('content')
    <div class="card">
        <div class="card-body">
            <form class="" method="post" action="{{ $formOptions['action'] }}">
                @csrf
                @if(request()->route()->getName() == 'admin.gift.show')
                    @method('PUT')
                @endif
                <div class="row mb-1">
                    <div class="col-12">
                        <label class="form-label" for="form-code">{{ gift::ATTRIBUTES_TEXT['code'] ?? '' }}<span class="text-danger">(*)</span></label>
                        <input type="text" id="form-code" class="form-control" name="code" value="{{ $default_values['code'] }}" placeholder="Mã đại lý" required>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-12">
                        <label class="form-label" for="form-wholesale_price">{{ gift::ATTRIBUTES_TEXT['name'] ?? '' }}<span class="text-danger">(*)</span></label>
                        <input type="text" id="form-name" class="form-control" name="name" value="{{ $default_values['name'] }}" placeholder="Tên" required/>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-12">
                        <label class="form-label" for="form-code">{{ gift::ATTRIBUTES_TEXT['price'] ?? '' }}<span class="text-danger">(*)</span></label>
                        <input type="number" id="form-address" class="form-control" name="price" value="{{ $default_values['price'] }}" placeholder="Giá tiền">
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-12">
                        <label class="form-label" for="form-code">Sản phẩm</label>
                        <select id="form-locality_ids" class="form-control form-organization_id has-select2"
                                name="product_id">
                            <option value="">- Sản phẩm -</option>
                            @foreach($formOptions['products'] as $key => $product)
                                <option value="{{ $key }}" {{ $key == $default_values['product_id'] ? 'selected' : '' }}>
                                    {{ $product }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-success me-1">
                        {{ request()->route()->getName() == 'admin.gift.show' ? 'Cập nhật' : 'Tạo mới' }}
                    </button>

                    <a href="{{ route('admin.gift.index') }}" class="btn btn-secondary me-1"><i data-feather='skip-back'></i> Quay lại</a>
                </div>
            </form>
        </div>
    </div>
@endsection
