<?php

use \App\Models\Agency;
use App\Models\Organization;

?>

@extends('layouts.main')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="" method="post" action="{{ route('admin.system-variable.update') }}">
                @csrf
                @foreach($functionVariables as $functionName => $variables)
                    <h5 class="mt-2">{{ $functionName }}</h5>
                    @foreach($variables as $variable)
                        <div class="mt-1">
                            <label class="form-label" for="form-code">
                                {{ $variable['display_name'] }} <span class="text-danger">(*)</span>
                            </label>
                            <div class="input-group">
                                <input type="text" id="" class="form-control" name="variables[{{ $variable['id'] }}][value]" value="{{ $variable['value'] }}"/>
                            </div>
                        </div>
                    @endforeach
                @endforeach

                <div class="mt-2">
                    <button type="submit" class="btn btn-success me-1">Cập nhật</button>
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
