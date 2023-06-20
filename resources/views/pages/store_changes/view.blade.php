@php
    use App\Models\Store;
    use App\Models\StoreChange;
    $store = $formOptions['compareData']['store'] ?? [];
    $storeChange = $formOptions['compareData']['storeChange'] ?? [];
@endphp
@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body">
            @if(isset($formOptions['exist_store']) && $formOptions['exist_store']->isNotEmpty())
                <div class="card-body border border-danger mb-1">
                    <div class="mb-1">Nhà thuốc này có thông tin trùng với:</div>
                    @foreach($formOptions['exist_store'] as $key => $item)
                        <div
                            class="mb-1">
                            {{ ($key + 1).'/ ' }}
                            {!! $item !!}
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="table-responsive mb-2">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Thuộc tính</th>
                        <th>Hiện tại</th>
                        <th>Thay đổi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(isset($store['name']) || isset($storeChange['name']))
                        <tr>
                            <td>Tên NT</td>
                            <td>{!! $store['name'] !!}</td>
                            <td>{!! $storeChange['name'] !!}</td>
                        </tr>
                    @endif
                    @if(isset($store['code']))
                        <tr>
                            <td>Mã NT</td>
                            <td>{{ $store['code'] ?? '' }}</td>
                            <td>{{ $store['code'] ?? '' }}</td>
                        </tr>
                    @endif
                    @if(isset($store['type_name']) || isset($storeChange['type_name']))
                        <tr>
                            <td>Loại NT</td>
                            <td>{!! $store['type_name'] ?? null !!}</td>
                            <td>{!! $storeChange['type_name'] ?? null !!}</td>
                        </tr>
                    @endif
                    @if(isset($store['lng_lat']) || isset($storeChange['lng_lat']))
                        <tr>
                            <td>Kinh độ, vĩ độ</td>
                            <td>{!! $store['lng_lat'] ?? null !!}</td>
                            <td>{!! $storeChange['lng_lat'] ?? null !!}</td>
                        </tr>
                    @endif
                    @if(isset($store['full_address']) || isset($storeChange['full_address']))
                        <tr>
                            <td>Địa chỉ</td>
                            <td>{!! $store['full_address'] ?? null !!}</td>
                            <td>{!! $storeChange['full_address'] ?? null !!}</td>
                        </tr>
                    @endif
                    @if(isset($store['locality_name']) || isset($storeChange['locality_name']))
                        <tr>
                            <td>Địa bàn</td>
                            <td>{!! $store['locality_name'] ?? null !!}</td>
                            <td>{!! $storeChange['locality_name'] ?? null !!}</td>
                        </tr>
                    @endif
                    @if(isset($store['phone_owner']) || isset($storeChange['phone_owner']))
                        <tr>
                            <td>SĐT nhận TT</td>
                            <td>{!! $store['phone_owner'] ?? null !!}</td>
                            <td>{!! $storeChange['phone_owner'] ?? null !!}</td>
                        </tr>
                    @endif
                    @if(isset($store['phone_web']) || isset($storeChange['phone_web']))
                        <tr>
                            <td>SĐT điểm bán</td>
                            <td>{!! $store['phone_web'] ?? null !!}</td>
                            <td>{!! $storeChange['phone_web'] ?? null !!}</td>
                        </tr>
                    @endif
                    @if(isset($current_store['files']) || isset($default_values['files']))
                        <tr>
                            <td>Hình ảnh</td>
                            <td>
                                @if (isset($current_store['files']) && $current_store['files']->count())
                                    <div class="image-thumbs">
                                        @foreach ($current_store['files'] as $item)
                                            <x-ZoomImage path="{{ Helper::getImagePath($item->source) }}"></x-ZoomImage>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if (isset($default_values['files']) && $default_values['files']->count())
                                    <div class="image-thumbs">
                                        @foreach ($default_values['files'] as $item)
                                            <x-ZoomImage path="{{ Helper::getImagePath($item->source) }}"></x-ZoomImage>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endif
                    @if(isset($store['show_web']) || isset($storeChange['show_web']))
                        <tr>
                            <td>Hiển thị điểm bán</td>
                            <td>{!! $store['show_web_text'] ?? null !!}</td>
                            <td>{!! $storeChange['show_web_text'] ?? null !!}</td>
                        </tr>
                    @endif
                    @if(isset($store['parent_text']) || isset($storeChange['parent_text']))
                        <tr>
                            <td>Nhà thuốc cha</td>
                            <td>{!! $store['parent_text'] ?? null !!}</td>
                            <td>{!! $storeChange['parent_text'] ?? null !!}</td>
                        </tr>
                    @endif
                    @if(isset($store['vat_info']) || isset($storeChange['vat_info']))
                        <tr>
                            <td>Thông tin viết hóa đơn</td>
                            <td>{!! $store['vat_info'] ?? null !!}</td>
                            <td>{!! $storeChange['vat_info'] ?? null !!}</td>
                        </tr>
                    @endif
                    @if(isset($store['status_text']) || isset($storeChange['status_text']))
                        <tr>
                            <td>Trạng thái</td>
                            <td>{!! $store['status_text'] ?? null !!}</td>
                            <td>{!! $storeChange['status_text'] ?? null !!}</td>
                        </tr>
                    @endif
                    @if(isset($store['tdv_text']) || isset($storeChange['tdv_text']))
                        <tr>
                            <td>Trình dược viên</td>
                            <td>{!! $store['tdv_text'] ?? null !!}</td>
                            <td>{!! $storeChange['tdv_text'] ?? null !!}</td>
                        </tr>
                    @endif
                    @if(isset($store['note_private']) || isset($storeChange['note_private']))
                        <tr>
                            <td>Ghi chú</td>
                            <td>{!! $store['note_private'] ?? null !!}</td>
                            <td>{!! $storeChange['note_private'] ?? null !!}</td>
                        </tr>
                    @endif
                    @if(isset($store['creator_name']) || isset($storeChange['creator_name']))
                        <tr>
                            <td>Nguời tạo</td>
                            <td>{!! $store['creator_name'] ?? null !!}</td>
                            <td>{!! $storeChange['creator_name'] ?? null !!}</td>
                        </tr>
                    @endif
                    @if(isset($formOptions['userUpdate']->name))
                        <tr>
                            <td>Người duyệt</td>
                            <td></td>
                            <td>{{ $formOptions['userUpdate']->name }}</td>
                        </tr>
                    @endif
                    @if($default_values['status'] == StoreChange::STATUS_NOT_APPROVE)
                        <tr>
                            <td>Lý do không duyệt</td>
                            <td></td>
                            <td>{{ $default_values['reason'] ?? null }}</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>

            <div class="text-center">
                @if($default_values['status'] == StoreChange::STATUS_INACTIVE)
                    <a href="{{ route('admin.store_changes.edit', $default_values['id']) }}"
                       class="btn btn-primary me-1 mb-1">
                        <i data-feather="edit" style="color: #FFFFFF !important;"></i> Sửa NT</a>
                @endif
                <a href="{{ route('admin.store_changes.index') }}" class="btn btn-secondary me-1 mb-1"><i
                        data-feather='rotate-ccw'></i> Quay lại</a>
            </div>
        </div>
    </div>
@endsection
