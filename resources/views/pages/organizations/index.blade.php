@extends('layouts.main')
@push('content-header')
    @can('them_cay_so_do')
        <div class="col ms-auto">
            @include('component.btn-add', ['title' => 'Thêm mới', 'href' => route('admin.organizations.create')])
        </div>
    @endcan
@endpush
@section('content')
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row flex-row-reverse">
                        <div class="col">
                            {!!
                                \App\Helpers\SearchFormHelper::getForm(
                                    route('admin.organizations.index'),
                                    'GET',
                                    [
                                        [
                                            "type" => "text",
                                            "name" => "search[name]",
                                            "placeholder" => "Tên",
                                            "defaultValue" => request('search.name'),
                                        ],
                                        [
                                            "type" => "selection",
                                            "name" => "search[type]",
                                            "defaultValue" => request('search.type', null),
                                            "options" => ["" => "- Loại -"] + \App\Models\Organization::TYPE_TEXTS,
                                        ],
                                    ],
                                )
                            !!}
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tableProducts" class="table table-bordered table-striped overflow-hidden">
                        <thead>
                        <tr>
                            <th class="text-center">STT</th>
                            <th class="">Tên</th>
                            <th class="">Loại</th>
                            <th class="">Tỉnh (địa bàn)</th>
                            <th class="">Quận</th>
                            <th class="">Tài khoản</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end">Chức năng</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($results as $item)
                            <tr>
                                <td class="text-center">{{ ($loop->index + 1) }}</td>
                                <td class="text-nowrap">
                                    <span style="padding-left: {{ $item->level * 2 .'rem' }}">
                                        {{ $item->name }}
                                    </span>
                                </td>
                                <td class="text-nowrap">{{ $item->type_name }}</td>
                                <td class="text-nowrap">{{ $item->province_name }}</td>
                                <td>
                                    @foreach($item->district_names as $_d_name)
                                        <span class="badge bg-success">{{ $_d_name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($item->users as $item_user)
                                        <a class="badge badge-light-primary"
                                           @can('sua_nguoi_dung')
                                               href="{{ route('admin.users.edit', $item_user->id) }}"
                                           @endcan
                                           target="_blank">
                                            @php
                                                $gName = $item_user?->product_groups?->pluck('name')->join(', ') ?? '';
                                            @endphp
                                            {{ $item_user->username }}{{ $gName ? '('.$gName.')' : '' }}
                                        </a>
                                    @endforeach
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $item->status ? 'success' : 'secondary' }}">
                                        {{ $item->status_name }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    @can('sua_cay_so_do')
                                        <a class="btn btn-sm btn-icon"
                                           href="{{ route('admin.organizations.edit', $item->id) }}">
                                            <i data-feather="edit" class="font-medium-2 text-body"></i>
                                        </a>
                                    @endcan
                                    @can('xoa_cay_so_do')
                                        <button class="btn btn-sm btn-icon delete-record" type="button"
                                                data-action="{{ route('admin.organizations.destroy', $item->id) }}">
                                            <i data-feather="trash-2" class="font-medium-2 text-body"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @can('xoa_cay_so_do')
        @include('component.model-delete')
    @endcan
@endsection
