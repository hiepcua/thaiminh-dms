@php
    use App\Models\Store;
    use App\Models\NewStore;
@endphp
@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-2">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Thuộc tính</th>
                            <th>Nội dung</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($default_values['name'])
                            <tr>
                                <td>Tên NT</td>
                                <td>{{ $default_values['name'] }}</td>
                            </tr>
                        @endif
                        @if($default_values['code'])
                            <tr>
                                <td>Mã NT</td>
                                <td>{{ $default_values['code'] }}</td>
                            </tr>
                        @endif
                        @if(Store::STORE_TYPE[$default_values['type']])
                            <tr>
                                <td>Loại NT</td>
                                <td>{{ Store::STORE_TYPE[$default_values['type']] }}</td>
                            </tr>
                        @endif
                        @php
                            $arrLatLng = [];
                            isset($default_values['lng']) && $default_values['lng'] ? $arrLatLng[] = $default_values['lng'] : null;
                            isset($default_values['lat']) && $default_values['lat'] ? $arrLatLng[] = $default_values['lat'] : null;
                        @endphp
                        @if(count($arrLatLng))
                            <tr>
                                <td>Kinh độ, vĩ độ</td>
                                <td>{{ implode(', ', $arrLatLng) }}</td>
                            </tr>
                        @endif

                        @php
                            $addressInfo = [];
                            isset($default_values['address']) && $default_values['address'] ? $addressInfo[] = $default_values['address'] : null;
                            isset($formOptions['ward']) && $formOptions['ward']?->ward_name ? $addressInfo[] = $formOptions['ward']?->ward_name : null;
                            isset($formOptions['district']) && $formOptions['district']?->district_name ? $addressInfo[] = $formOptions['district']?->district_name : null;
                            isset($formOptions['province']) && $formOptions['province']?->province_name ? $addressInfo[] = $formOptions['province']?->province_name : null;
                        @endphp
                        @if(count($addressInfo))
                            <tr>
                                <td>Địa chỉ</td>
                                <td>{{ implode(' - ', $addressInfo) }}</td>
                            </tr>
                        @endif

                        @if(isset($default_values['organization']->name))
                            <tr>
                                <td>Địa bàn</td>
                                <td>{{ $default_values['organization']->name }}</td>
                            </tr>
                        @endif

                        @if($default_values['phone_owner'])
                            <tr>
                                <td>SĐT nhận TT</td>
                                <td>{{ $default_values['phone_owner'] }}</td>
                            </tr>
                        @endif

                        @if($default_values['phone_web'] != '')
                            <tr>
                                <td>SĐT điểm bán</td>
                                <td>{{ $default_values['phone_web'] }}</td>
                            </tr>
                        @endif

                        @if (isset($formOptions['files']) && $formOptions['files']->count())
                            <tr>
                                <td>Hình ảnh</td>
                                <td>
                                    <div class="image-thumbs">
                                        @foreach ($formOptions['files'] as $item)
                                            <input type="hidden" name="old_files[]" value="{{ $item->source }}">
                                            <x-ZoomImage path="{{ Helper::getImagePath($item->source) }}"></x-ZoomImage>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endif

                        @php
                            $showWeb = $default_values['show_web'] ?? Store::SHOW_WEB;
                        @endphp
                        <tr>
                            <td>Hiển thị điểm bán</td>
                            <td>{{ $showWeb ? 'Có' : 'Không' }}</td>
                        </tr>

                        @php
                            $parentText = '';
                            $hasParent = $default_values['parent_id'] ?? '';
                            if(isset($formOptions['parent_store']) && $formOptions['parent_store']->count()){
                                $parentStoreAddressInfo = [
                                    $formOptions['parent_store']->address,
                                    $formOptions['parent_store']?->ward?->ward_name,
                                    $formOptions['parent_store']?->district?->district_name,
                                    $formOptions['parent_store']?->province?->province_name,
                                ];

                                $parentText = $parentText . ($formOptions['parent_store']->code != '' ? $formOptions['parent_store']->code . ' - ' : '');
                                $parentText = $parentText . ($formOptions['parent_store']->name != '' ? $formOptions['parent_store']->name . ' - ' : '');
                                $parentText = $parentText . implode(' - ', $parentStoreAddressInfo);
                            }
                        @endphp
                        <tr>
                            <td>Nhà thuốc cha</td>
                            <td>{{ $hasParent ? $parentText : 'Không' }}</td>
                        </tr>

                        @php
                            $vatInfo = [];
                            $default_values['vat_number'] ? $vatInfo[] =  '<b>MST</b>: '.$default_values['vat_number'] : null;
                            $default_values['vat_company'] ? $vatInfo[] =  '<b>Tên công ty</b>: '.$default_values['vat_company'] : null;
                            $default_values['vat_address'] ? $vatInfo[] =  '<b>Địa chỉ</b>: '.$default_values['vat_address'] : null;
                            $default_values['vat_buyer'] ? $vatInfo[] =  '<b>Người mua</b>: '.$default_values['vat_buyer'] : null;
                            $default_values['vat_email'] ? $vatInfo[] =  '<b>Email</b>: '.$default_values['vat_email'] : null;
                        @endphp

                        @if(count($vatInfo))
                            <tr>
                                <td>Thông tin viết hóa đơn</td>
                                <td>{!! implode(' - ', $vatInfo) !!}</td>
                            </tr>
                        @endif

                        @php
                            $lineName = isset($formOptions['localityLines']) ?
                            $formOptions['localityLines']->firstWhere('id', $default_values['line'] ?? '')?->name : null;
                        @endphp
                        @if($lineName)
                            <tr>
                                <td>Tuyến</td>
                                <td>{{ $lineName }}</td>
                            </tr>
                        @endif

                        @if(isset($formOptions['lineStore']->number_visit))
                            <tr>
                                <td>Số lần thăm/tháng</td>
                                <td>{{ $formOptions['lineStore']->number_visit }}</td>
                            </tr>
                        @endif

                        @if(isset($formOptions['tdv']))
                            @php
                                $listTDV = '';
                                foreach($formOptions['tdv'] as $item){
                                    $listTDV = $listTDV . '<span>'.$item->name.'</span>, ';
                                }
                            @endphp

                            <tr>
                                <td>Trình dược viên</td>
                                <td>{!! $listTDV ?? '' !!}</td>
                            </tr>
                        @endif

                        @if($default_values['note_private'])
                            <tr>
                                <td>Ghi chú</td>
                                <td>{{ $default_values['note_private'] }}</td>
                            </tr>
                        @endif

                        @if ($formOptions['status'][$default_values['status']])
                            <tr>
                                <td>Trạng thái</td>
                                <td>{{ $formOptions['status'][$default_values['status']] }}</td>
                            </tr>
                        @endif

                        @if(isset($formOptions['user']))
                            <tr>
                                <td>Người tạo</td>
                                <td>{{ $formOptions['user']->name }}</td>
                            </tr>
                        @endif

                        @if(isset($formOptions['userUpdate']->name))
                            <tr>
                                <td>Người duyệt</td>
                                <td>{{ $formOptions['userUpdate']->name }}</td>
                            </tr>
                        @endif

                        @if($default_values['status'] == NewStore::STATUS_NOT_APPROVED)
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
                @if($default_values['status'] == \App\Models\NewStore::STATUS_INACTIVE)
                    <a href="{{ route('admin.tdv.new-stores.edit', $default_values['newStoreId']) }}"
                       class="btn btn-primary me-1 mb-1">
                        <i data-feather="edit" style="color: #FFFFFF !important;"></i> Sửa NT</a>
                @endif

                <a href="{{ route('admin.tdv.new-stores.index') }}" class="btn btn-secondary me-1 mb-1"><i
                        data-feather='rotate-ccw'></i> Quay lại</a>
            </div>
        </div>
    </div>
@endsection
