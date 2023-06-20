@php
    use App\Models\NewStore;
    use App\Models\Store;
@endphp
@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body">
            <form id="form-approve" class="has-provinces" method="post" action="{{ $formOptions['action'] }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')
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
                            <th>Nội dung</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Tên nhà thuốc</td>
                            <td>{{ $default_values['name'] ?? '' }}</td>
                        </tr>

                        <tr>
                            <td>Mã nhà thuốc</td>
                            <td>
                                @if(($default_values['status'] == NewStore::STATUS_INACTIVE || $default_values['status'] == NewStore::STATUS_ALL) &&
                                    $default_values['is_disabled'] == NewStore::STATUS_UN_DISABLE)
                                    <input type="text" name="code" id="form-code" class="form-control"
                                           value="{{ $default_values['code'] ?? '' }}">
                                @else
                                    {{ $default_values['code'] ?? '' }}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>Loại nhà thuốc</td>
                            <td>
                                {{ Store::STORE_TYPE[$default_values['type']] }}
                            </td>
                        </tr>

                        @php
                            $arrLatLong = [];
                            $default_values['lng'] != '' ? $arrLatLong[] = $default_values['lng'] : null;
                            $default_values['lat'] != '' ? $arrLatLong[] = $default_values['lat'] : null;
                        @endphp

                        <tr>
                            <td>Kinh độ, vĩ độ</td>
                            <td>{{ count($arrLatLong) ? implode('-', $arrLatLong) : null }}</td>
                        </tr>

                        <tr>
                            <td>Địa chỉ</td>
                            <td>
                                @if($default_values['address'] != '')
                                    @php
                                        $addressInfo = [];
                                        $addressInfo[] = $default_values['address'];
                                        $formOptions['ward'] != '' ? $addressInfo[] = $formOptions['ward']?->ward_name : null;
                                        $formOptions['district'] != '' ? $addressInfo[] = $formOptions['district']?->district_name : null;
                                        $formOptions['province'] != '' ? $addressInfo[] = $formOptions['province']?->province_name : null;
                                    @endphp
                                    {{ count($addressInfo) ? implode(' - ', $addressInfo) : null }}
                                @endif
                            </td>
                        </tr>

                        @if(isset($default_values['localityName']))
                            <tr>
                                <td>Địa bàn</td>
                                <td>{{ $default_values['localityName'] }}</td>
                            </tr>
                        @endif

                        <tr>
                            <td>SĐT nhận TT</td>
                            <td>
                                {{ $default_values['phone_owner'] != '' ? $default_values['phone_owner'] : null }}
                            </td>
                        </tr>

                        <tr>
                            <td>SĐT điểm bán</td>
                            <td>{{ $default_values['phone_web'] != '' ? $default_values['phone_web'] : null }}</td>
                        </tr>

                        <tr>
                            <td>Hình ảnh</td>
                            <td>
                                @if ($formOptions['files']->count())
                                    <div class="image-thumbs">
                                        @foreach ($formOptions['files'] as $item)
                                            <input type="hidden" name="old_files[]" value="{{ $item->source }}">
                                            <x-ZoomImage path="{{ Helper::getImagePath($item->source) }}"/>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>Nhà thuốc cha</td>
                            <td>
                                @if(isset($formOptions['parent_store']) && $formOptions['parent_store']->count())
                                    @php
                                        $parentStoreAddressInfo = [
                                            $formOptions['parent_store']->address,
                                            $formOptions['parent_store']?->ward?->ward_name,
                                            $formOptions['parent_store']?->district?->district_name,
                                            $formOptions['parent_store']?->province?->province_name,
                                        ];
                                        $parentText = '';
                                        $parentText = $parentText . ($formOptions['parent_store']->code != '' ? $formOptions['parent_store']->code . ' - ' : '');
                                        $parentText = $parentText . ($formOptions['parent_store']->name != '' ? $formOptions['parent_store']->name . ' - ' : '');
                                        $parentText = $parentText . implode(' - ', $parentStoreAddressInfo);
                                    @endphp
                                    {{ $parentText }}
                                @endif
                            </td>
                        </tr>

                        @php
                            $vatInfo = [];
                            $default_values['vat_number'] ? $vatInfo[] =  '<b>MST</b>: '.$default_values['vat_number'] : null;
                            $default_values['vat_buyer'] ? $vatInfo[] =  '<b>Người mua</b>: '.$default_values['vat_buyer'] : null;
                            $default_values['vat_company'] ? $vatInfo[] =  '<b>Tên công ty</b>: '.$default_values['vat_company'] : null;
                            $default_values['vat_address'] ? $vatInfo[] =  '<b>Địa chỉ</b>: '.$default_values['vat_address'] : null;
                            $default_values['vat_email'] ? $vatInfo[] =  '<b>Email</b>: '.$default_values['vat_email'] : null;
                        @endphp

                        <tr>
                            <td>Thông tin viết hóa đơn</td>
                            <td>
                                @if(count($vatInfo))
                                    {!! implode(' - ', $vatInfo) !!}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>Tuyến</td>
                            <td>
                                @if(isset($formOptions['line']))
                                    {{ $formOptions['line']?->name }}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>Số lần thăm/ tháng</td>
                            <td>
                                @if(isset($formOptions['lineStore']))
                                    {{ $formOptions['lineStore']?->number_visit }}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>Ghi chú</td>
                            <td>
                                @if($default_values['note_private'])
                                    {{ $default_values['note_private'] }}
                                @endif
                            </td>
                        </tr>

                        @php
                            $listTDV = '';
                            if($formOptions['tdv']){
                                foreach($formOptions['tdv'] as $item){
                                    $listTDV = $listTDV . '<span>'.$item->name.'</span>, ';
                                }
                            }
                        @endphp

                        <tr>
                            <td>TDV</td>
                            <td>
                                @if($listTDV)
                                    {!! $listTDV !!}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>Người tạo</td>
                            <td>
                                @if($formOptions['user'])
                                    {{ $formOptions['user']->name }}
                                @endif
                            </td>
                        </tr>

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

                <div class="text-center">
                    @if(in_array(intval($default_values['status']), [NewStore::STATUS_INACTIVE, NewStore::STATUS_ALL]) && !$default_values['is_disabled'])
                        <input type="hidden" id="form-status" name="storeStatus" value="">
                        <button type="button" id="btn-approve" class="btn btn-success me-1 mb-1"><i
                                data-feather='thumbs-up'></i>
                            Duyệt
                        </button>
                        <button type="button" id="btn-not-approve" class="btn btn-secondary me-1 mb-1"
                                data-bs-toggle="modal" data-bs-target="#reason-not-approve"><i
                                data-feather='thumbs-down'></i> {{ NewStore::STATUS_TEXTS[NewStore::STATUS_NOT_APPROVED] }}
                        </button>
                        <button type="button" id="btn-delete" class="btn btn-danger me-1 mb-1"
                                data-action="{{ route('admin.new-stores.destroy', $new_store_id) }}"><i
                                data-feather='x'></i> Xóa
                        </button>
                        <a href="{{ route('admin.new-stores.edit', $new_store_id) }}"
                           class="btn btn-primary me-1 mb-1"><i data-feather="edit" class="font-medium-2 text-body"
                                                                style="color: #fff !important;"></i> Sửa</a>
                    @endif

                    @if($default_values['is_disabled'])
                        <button type="button" class="btn btn-danger me-1 mb-1">Đã xóa
                        </button>
                    @endif

                    <a href="{{ route('admin.new-stores.index') }}" class="btn btn-secondary me-1 mb-1"><i
                            data-feather='rotate-ccw'></i> Quay lại</a>
                </div>
            </form>
        </div>
    </div>
    <!-- === START: MODAL === -->
    @include('pages.new_stores.modal-reason-not-approve',['newStoreId' => $default_values['newStoreId'] ?? null])
    <!-- === END: MODAL === -->
@endsection

@push('scripts-custom')
    <script>
        let _form_approve = $('#form-approve'),
            _store_status = $('#form-status'),
            _store_code = $('#form-code'),
            _btn_approve = $('#btn-approve'),
            _btn_delete = $('#btn-delete');

        _btn_approve.on('click', function () {
            _store_status.val('{{ NewStore::STATUS_ACTIVE }}');
            _form_approve.submit();
        });

        _btn_delete.on('click', function () {
            Swal.fire({
                title: 'Bạn có chắc chắn muốn xóa nhà thuốc mới?',
                showDenyButton: true,
                confirmButtonText: 'Xóa',
                denyButtonText: 'Không',
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    ajax($(this).attr('data-action'), 'DELETE', null).done(async (response) => {
                        await Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        })

                        window.location.href = '{{ route('admin.new-stores.index') }}';
                    }).fail((error) => {
                        console.log(error);
                        alert('Server has an error. Please try again!');
                    });
                }
            })
        })
    </script>
@endpush
