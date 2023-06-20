<?php
    use App\Models\Organization;
    use App\Models\Promotion;
    use App\Models\PromotionCondition;
    use App\Helpers\Helper;
?>
@extends('layouts.main')
@section('page_title', $page_title)
@push('content-header')
    @can('them_chuong_trinh_khuyen_mai')
        <div class="col ms-auto">
            @include('component.btn-add', ['title'=>'Thêm mới', 'href'=>route('admin.promotion.create')])
        </div>
    @endcan
@endpush
@section('content')
    <section class="app-user-list">
        <div class="row" id="table-striped">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row flex-wrap">
                            <div class="col">
                            {!!
                                \App\Helpers\SearchFormHelper::getForm(
                                    route('admin.promotion.index'),
                                    'GET',
                                    [
                                        [
                                            "type" => "text",
                                            "name" => "search[name]",
                                            "placeholder" => "Tên",
                                            "defaultValue" => request('search.name'),
                                        ],
                                        [
                                            "type" => "divisionPicker",
                                            "divisionPickerConfig" => [
                                                "currentUser" => \App\Helpers\Helper::currentUser(),
                                                "hasRelationship" => true,
                                                "activeTypes" => [
                                                    Organization::TYPE_KHU_VUC,
                                                ],
                                                "excludeTypes" => [Organization::TYPE_DIA_BAN],
                                                "setup" => [
                                                    'multiple' => true,
                                                    'name' => 'search[division_id][]',
                                                    'class' => '',
                                                    'id' => 'division_id',
                                                    'attributes' => '',
                                                    'selected' => request('search.division_id', null)
                                                ]
                                            ],
                                        ],
                                        [
                                            "type" => "datepicker",
                                            "placeholder" => "Ngày",
                                            "name" => "search[date]",
                                            "defaultValue" => request('search.date'),
                                        ],
                                        [
                                            "type" => "selection",
                                            "name" => "search[type]",
                                            "defaultValue" => request('search.type', null),
                                            "options" => ["" => "- Loại -"] + $formOptions['types'],
                                        ],
                                        [
                                            "type" => "selection",
                                            "name" => "search[status]",
                                            "defaultValue" => request('search.status', null),
                                            "options" => ["" => "- Trạng thái -"] + $formOptions['status'],
                                        ],
                                    ],
                                )
                            !!}
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="tableProducts" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tên</th>
                                    <th>Mô tả</th>
                                    <th>Khu vực áp dụng</th>
                                    <th>Khu vực loại trừ</th>
                                    <th>Ngày bắt đầu</th>
                                    <th>Ngày kết thúc</th>
                                    <th>Trạng thái</th>
                                    <th>Người tạo</th>
                                    <th>Chức năng</th>
                                </tr>
                            </thead>
                            <tbody>
                            @if(count($promotions))
                            @foreach( $promotions as $promotion )
                                <tr>
                                    <td>{{ $promotion->name }}</td>
                                    <td>{{ $promotion->desc }}</td>
                                    <td>
                                        @foreach($promotion->organizations as $organization)
                                            <span class="badge bg-success">
                                                {{ $organization->name }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($promotion->organizationsExclude as $organization)
                                            <span class="badge bg-secondary">
                                                {{ $organization->name }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td>{{ \Carbon\Carbon::create($promotion->started_at)->format('d-m-Y H:i:s') }}</td>
                                    <td>{{ \Carbon\Carbon::create($promotion->ended_at)->format('d-m-Y H:i:s') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $promotion->status == Promotion::STATUS_ACTIVE ? 'success' : 'secondary' }}">
                                            {{ Promotion::STATUS_TEXTS[$promotion->status] ?? '' }}
                                        </span>
                                    </td>
                                    <td>{{ $promotion->createdBy->name }}</td>
                                    <td>
                                        @can('sua_chuong_trinh_khuyen_mai')
                                            <a class="btn btn-sm btn-icon"
                                               href="{{ route('admin.promotion.show', $promotion->id) }}">
                                                <i data-feather="edit" class="font-medium-2 text-body"></i>
                                            </a>
                                        @endcan
                                        @can('xoa_chuong_trinh_khuyen_mai')
                                            <button class="btn-delete-promotion btn btn-sm btn-icon delete-record waves-effect waves-float waves-light"
                                                type="button"
                                                data-action="{{ route('admin.promotion.destroy', $promotion->id) }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather text-danger feather-trash font-medium-2 text-body"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                            @else
                                <tr>
                                    <td colspan="9" class="text-center">Không có dữ liệu</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 mt-1 d-flex justify-content-center">
                            {{ $promotions->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/core/pages/promotion/index.js') }}"></script>
@endpush
