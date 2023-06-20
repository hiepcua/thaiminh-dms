<?php

use App\Helpers\SearchFormHelper;

$idFormSearch = now()->timestamp
?>
<form class="row component-search-form w-100 {{ $class }}" {{ $attributes }} action="{{ $route }}"
      method="{{ $method }}">
    @foreach($config as $input)
        <div
            class="{{ $input['wrapClass'] ?? 'col-md-auto' }} mb-1 p-0 ms-1 component-search-input {{ ($input['class'] ?? '') }}">
            {!!
                SearchFormHelper::getInput(
                    $input['type'] ?? '',
                    $input['name'] ?? '',
                    $input['placeholder'] ?? '',
                    $input['defaultValue'] ?? '',
                    ($input['class'] ?? '') . ' w-100',
                    $input['id'] ?? '',
                    $input['options'] ?? [],
                    $input['attributes'] ?? '',
                    $input['divisionPickerConfig'] ?? [],
                    $input['other_options'] ?? [],
                    $input['yearMonthWeekConfig'] ?? [],
                )
            !!}
        </div>
    @endforeach
    <label class="col-auto mb-1 p-0 ms-1 wrap-button-search">
        <button class="btn btn-primary btn-icon" type="submit">
            <i data-feather="search"></i>
            Tìm kiếm
        </button>
        @if(request()->route()->getName() == 'admin.tdv.store.index')
            <button type="button" class="btn btn-primary waves-effect waves-float waves-light" data-bs-toggle="modal"
                    data-bs-target="#store-advanced-search">
                Nâng cao
            </button>
        @endif
    </label>
    @can($permissionExport)
        <label class="col-auto mb-1">
            <button type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#modal_export_{{$idFormSearch}}"
                    data-href="{{ route($routeExport, array_merge(
                        [
                            'totalRecord' => $totalRecordExport,
                            'hash_id' => request('search.hash_id') ?: md5('505_' . time())
                        ],
                        request()->all(),
                        ))
                }}"
                    class="btn btn-icon btn-outline-dark export-js">
                <i data-feather='download'></i>
                Export
            </button>
        </label>
    @endcan
</form>
@can($permissionExport)
    @include('snippets.modal-export-progress', ['idExportModal' => "modal_export_$idFormSearch"])
@endcan
@if(request()->route()->getName() == 'admin.tdv.store.index')
    @include('snippets.modal-store-advanced-search', [
                        'numberDayNotOrder' => request('search.number_day_not_order') ?? null,
                        'notEnoughVisit' => request('search.not_enough_visit') ?? null])
@endif
