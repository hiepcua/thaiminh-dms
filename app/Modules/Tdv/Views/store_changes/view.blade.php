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
            <div class="row mb-1">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Thuộc tính</th>
                            <th>Nội dung</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(isset($storeChange['name']))
                            <tr>
                                <td>Tên NT</td>
                                <td>{!! $storeChange['name'] !!}</td>
                            </tr>
                        @endif
                        @if(isset($storeChange['code']))
                            <tr>
                                <td>Mã NT</td>
                                <td>{{ $storeChange['code'] ?? '' }}</td>
                            </tr>
                        @endif
                        @if(isset($storeChange['type_name']))
                            <tr>
                                <td>Loại NT</td>
                                <td>{!! $storeChange['type_name'] ?? null !!}</td>
                            </tr>
                        @endif
                        @if(isset($storeChange['lng_lat']))
                            <tr>
                                <td>Kinh độ, vĩ độ</td>
                                <td>{!! $storeChange['lng_lat'] ?? null !!}</td>
                            </tr>
                        @endif
                        @if(isset($storeChange['full_address']))
                            <tr>
                                <td>Địa chỉ</td>
                                <td>{!! $storeChange['full_address'] ?? null !!}</td>
                            </tr>
                        @endif
                        @if(isset($storeChange['locality_name']))
                            <tr>
                                <td>Địa bàn</td>
                                <td>{!! $storeChange['locality_name'] ?? null !!}</td>
                            </tr>
                        @endif
                        @if(isset($storeChange['phone_owner']))
                            <tr>
                                <td>SĐT nhận TT</td>
                                <td>{!! $storeChange['phone_owner'] ?? null !!}</td>
                            </tr>
                        @endif
                        @if(isset($storeChange['phone_web']))
                            <tr>
                                <td>SĐT điểm bán</td>
                                <td>{!! $storeChange['phone_web'] ?? null !!}</td>
                            </tr>
                        @endif
                        @if(isset($default_values['files']) && $default_values['files']->count())
                            <tr>
                                <td>Hình ảnh</td>
                                <td>
                                    <div class="image-thumbs">
                                        @foreach ($default_values['files'] as $item)
                                            <x-ZoomImage
                                                path="{{ Helper::getImagePath($item->source) }}"></x-ZoomImage>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endif
                        @if(isset($storeChange['show_web']))
                            <tr>
                                <td>Hiển thị điểm bán</td>
                                <td>{!! $storeChange['show_web_text'] ?? null !!}</td>
                            </tr>
                        @endif
                        @if(isset($storeChange['parent_text']))
                            <tr>
                                <td>Nhà thuốc cha</td>
                                <td>{!! $storeChange['parent_text'] ?? null !!}</td>
                            </tr>
                        @endif
                        @if(isset($storeChange['vat_info']))
                            <tr>
                                <td>Thông tin viết hóa đơn</td>
                                <td>{!! $storeChange['vat_info'] ?? null !!}</td>
                            </tr>
                        @endif
                        @if(isset($storeChange['status_text']))
                            <tr>
                                <td>Trạng thái</td>
                                <td>{!! $storeChange['status_text'] ?? null !!}</td>
                            </tr>
                        @endif
                        @if(isset($storeChange['tdv_text']))
                            <tr>
                                <td>Trình dược viên</td>
                                <td>{!! $storeChange['tdv_text'] ?? null !!}</td>
                            </tr>
                        @endif
                        @if(isset($storeChange['note_private']))
                            <tr>
                                <td>Ghi chú</td>
                                <td>{!! $storeChange['note_private'] ?? null !!}</td>
                            </tr>
                        @endif
                        @if(isset($storeChange['creator_name']))
                            <tr>
                                <td>Nguời tạo</td>
                                <td>{!! $storeChange['creator_name'] ?? null !!}</td>
                            </tr>
                        @endif
                        @if(isset($formOptions['userUpdate']->name))
                            <tr>
                                <td>Người duyệt</td>
                                <td>{{ $formOptions['userUpdate']->name }}</td>
                            </tr>
                        @endif

                        @if($default_values['status'] == StoreChange::STATUS_NOT_APPROVE)
                            <tr>
                                <td>Lý do không duyệt</td>
                                <td>{{ $default_values['reason'] ?? null }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-center">
                @if($canEdit)
                    <a href="{{ route('admin.tdv.store-changes.edit', $default_values['id']) }}"
                       class="btn btn-primary me-1 mb-1">
                        <i data-feather="edit" class="font-medium-2 text-body" style="color: #FFFFFF !important;"></i>
                        Sửa NT</a>
                @endif
                <a href="{{ route('admin.tdv.store-changes.index') }}" class="btn btn-secondary me-1 mb-1"><i
                        data-feather='rotate-ccw'></i> Quay lại</a>
            </div>
        </div>
    </div>
@endsection
