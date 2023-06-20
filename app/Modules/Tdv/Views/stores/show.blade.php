@php
    use \App\Models\Store;
    use App\Helpers\Helper;
    $currentUser        = Helper::currentUser();
    $userRole           = $currentUser?->roles[0]?->name;
    $canAddEditStore    = $currentUser->canany(['sua_nha_thuoc', 'tdv_sua_nha_thuoc', 'them_nha_thuoc', 'tdv_them_nha_thuoc']);
    $userLocalities     = $formOptions['userLocalities'] ?? [];
    $userProvinces      = $formOptions['userProvinces'] ?? [];
    $userDistricts      = $formOptions['userDistricts'] ?? [];
    $userWards          = $formOptions['userWards'] ?? [];
    $storeType          = Store::STORE_TYPE[$default_values['type'] ?? ''] ?? '';
    $storeName          = $default_values['name'] ?? '';
    $storeCode          = $default_values['code'] ?? '';
    $storePhoneOwner    = $default_values['phone_owner'] ?? '';
    $storePhoneWeb      = $default_values['phone_web'] ?? '';
    $storeDivisionName  = $localities->firstWhere('id', $default_values['organization_id'] ?? '')?->name;
    $provincesName      = $provinces ? $provinces->firstWhere('id', $default_values['province_id'] ?? null)?->province_name : '';
    $districtName       = $userDistricts ? $userDistricts->firstWhere('id', $default_values['district_id'] ?? null)?->district_name : '';
    $wardName           = $userWards ? $userWards->firstWhere('id', $default_values['ward_id'] ?? null)?->ward_name : '';
    $address            = $default_values['address'] ?? '';
    $vatBuyer           = $default_values['vat_buyer'] ?? '';
    $vatCompany         = $default_values['vat_company'] ?? '';
    $vatNumber          = $default_values['vat_number'] ?? '';
    $vatEmail           = $default_values['vat_email'] ?? '';
    $vatAddress         = $default_values['vat_address'] ?? '';
    $addressInfo        = [];
    $address !== "" ? $addressInfo[] = $address : null;
    $wardName !== "" ? $addressInfo[] = $wardName : null;
    $districtName !== "" ? $addressInfo[] = $districtName : null;
    $provincesName !== "" ? $addressInfo[] = $provincesName : null;
    $arrLatLng          = [];
    isset($default_values['lng']) && $default_values['lng'] ? $arrLatLng[] = $default_values['lng'] : null;
    isset($default_values['lat']) && $default_values['lat'] ? $arrLatLng[] = $default_values['lat'] : null;
@endphp
@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body">
            <div id="form-add-edit-store" class="has-provinces">
                <div class="mb-1 w-100">
                    @if(isset($checkedRecordCurrentStore))
                        <span class="badge bg-success rounded-3" style="padding: 5px 10px; cursor: pointer">
                            Checkout lúc: {{ $checkedRecordCurrentStore->checkout_at }}
                        </span>
                    @elseif(in_array($id, array_keys($storeCheckedInDay)))
                        <span class="badge bg-danger rounded-3 checkout-btn" store-id="{{ $id }}"
                              style="padding: 5px 10px; cursor: pointer">
                            Checkout
                        </span>
                        <span class="float-end badge bg-secondary rounded-3" id="refresh-store-list"
                              style="padding: 5px 10px; cursor: pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-refresh"
                                 width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                 fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"></path>
                                <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"></path>
                            </svg>
                            Refresh
                        </span>
                    @else
                        <span class="badge bg-warning rounded-3" id="checkin-btn"
                              style="padding: 5px 10px; cursor: pointer">
                            Checkin
                        </span>
                        <span class="float-end badge bg-secondary rounded-3" id="refresh-store-list"
                              style="padding: 5px 10px; cursor: pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-refresh"
                                 width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                 fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"></path>
                                <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"></path>
                            </svg>
                            Refresh
                        </span>
                    @endif
                </div>
                <div class="table-responsive mb-2">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Tiêu đề</th>
                            <th>Nội dung</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($storeName)
                            <tr>
                                <td>Tên NT</td>
                                <td>{{ $storeName }} - {{ $storeCode }} {{ $storeType ? "($storeType)" : '' }}</td>
                            </tr>
                        @endif
                        @if($storeType)
                            <tr>
                                <td>Loại NT</td>
                                <td>{{ $storeType }}</td>
                            </tr>
                        @endif
                        @if(count($arrLatLng))
                            <tr>
                                <td>Kinh độ, vĩ độ</td>
                                <td>{{ implode(', ', $arrLatLng) }}</td>
                            </tr>
                        @endif
                        @if(count($addressInfo))
                            <tr>
                                <td>Địa chỉ</td>
                                <td>{{ implode(' - ', $addressInfo) }}</td>
                            </tr>
                        @endif
                        @if($storeDivisionName)
                            <tr>
                                <td>Địa bàn</td>
                                <td>{{ $storeDivisionName }}</td>
                            </tr>
                        @endif
                        @if($storePhoneOwner)
                            <tr>
                                <td>SĐT nhận TT</td>
                                <td>{{ $storePhoneOwner }}</td>
                            </tr>
                        @endif
                        @if($storePhoneWeb)
                            <tr>
                                <td>SĐT điểm bán</td>
                                <td>{{ $storePhoneWeb }}</td>
                            </tr>
                        @endif
                        @if (isset($formOptions['files']) && count($formOptions['files']))
                            <tr>
                                <td>Hình ảnh</td>
                                <td>
                                    <div class="image-thumbs">
                                        @foreach ($formOptions['files'] ?? [] as $item)
                                            <input type="hidden" name="old_files[]" value="{{ $item->source }}">
                                            <x-ZoomImage path="{{ Helper::getImagePath($item->source) }}"
                                                         alt="{{ $item->name }}"></x-ZoomImage>
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
                            $hasParent = $default_values['parent_id'] ?? '';
                            if($hasParent && isset($formOptions) && $formOptions['parent_store']){
                                $parentStoreAddressInfo = [];
                                $formOptions['parent_store']->address ? $parentStoreAddressInfo[] = $formOptions['parent_store']->address : null;
                                $formOptions['parent_store']?->ward?->ward_name ? $parentStoreAddressInfo[] = $formOptions['parent_store']?->ward?->ward_name : null;
                                $formOptions['parent_store']?->district?->district_name ? $parentStoreAddressInfo[] = $formOptions['parent_store']?->district?->district_name : null;
                                $formOptions['parent_store']?->province?->province_name ? $parentStoreAddressInfo[] = $formOptions['parent_store']?->province?->province_name : null;

                                $parentText = '';
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
                            isset($default_values) && $default_values['vat_number'] ? $vatInfo[] =  '<b>MST</b>: '.$default_values['vat_number'] : null;
                            isset($default_values) && $default_values['vat_company'] ? $vatInfo[] =  '<b>Tên công ty</b>: '.$default_values['vat_company'] : null;
                            isset($default_values) && $default_values['vat_address'] ? $vatInfo[] =  '<b>Địa chỉ</b>: '.$default_values['vat_address'] : null;
                            isset($default_values) && $default_values['vat_buyer'] ? $vatInfo[] =  '<b>Người mua</b>: '.$default_values['vat_buyer'] : null;
                            isset($default_values) && $default_values['vat_email'] ? $vatInfo[] =  '<b>Email</b>: '.$default_values['vat_email'] : null;
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

                        @if ($default_values['note_private'])
                            <tr>
                                <td>Ghi chú nội bộ</td>
                                <td>{{ $default_values['note_private'] }}</td>
                            </tr>
                        @endif

                        @if ($formOptions['status'][$default_values['status']])
                            <tr>
                                <td>Trạng thái</td>
                                <td>{{ $formOptions['status'][$default_values['status']] }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>

                <div class="row mb-2">
                    <h5>Thông tin TDV</h5>
                    <div class="col-md-12 mb-1">
                        <div id="list-tdv"></div>
                    </div>
                </div>

                <div class="text-center">
                    @if($default_values['status'] == Store::STATUS_ACTIVE)
                        <a href="{{ route('admin.tdv.store.edit', $default_values['storeId']) }}"
                           class="btn btn-primary me-1 mb-1">
                            <i data-feather="edit" style="color: #FFFFFF !important;"></i> Sửa NT</a>
                    @endif
                    <a href="{{ route('admin.tdv.register-poster.index', $id) }}" class="btn btn-success me-1 mb-1">Đăng
                        ký
                        Poster</a>
                    <a href="{{ route('admin.tdv.store.index') }}" class="btn btn-secondary me-1 mb-1"><i
                            data-feather='rotate-ccw'></i> Quay lại</a>
                </div>
            </div>
        </div>
    </div>

    <!-- === START: MODAL === -->
    @include('pages.stores.modal-search')
    <!-- === END: MODAL === -->
@endsection

@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script>
        const ROUTE_GET_USER_BY_LOCALITY = "{{ route('admin.get-user-by-locality') }}";
        const ROUTE_CHECKIN_STORE = '{{ route('admin.tdv.checkin.checkin') }}';
        const ROUTE_CHECKOUT_STORE = '{{ route('admin.tdv.checkin.checkout') }}';

        $(document).ready(function () {
            let list_users = (locality_id) => {
                if (locality_id > 0) {
                    ajax(ROUTE_GET_USER_BY_LOCALITY, 'POST', {
                        locality_id: locality_id
                    }).done(function (response) {
                        $('#list-tdv').html(response.htmlString);
                    }).fail(function (error) {
                        console.log(error);
                        alert('Server has an error. Please try again!');
                    });
                }
            }

            list_users({{ $default_values['organization_id'] }});


            $('#refresh-store-list').on('click', function () {
                window.location.reload();
            })

            function showError(error) {
                let message = '';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        message += "User denied the request for Geolocation."
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message += "Location information is unavailable."
                        break;
                    case error.TIMEOUT:
                        message += "The request to get user location timed out."
                        break;
                    case error.UNKNOWN_ERROR:
                        message += "An unknown error occurred."
                        break;
                }
                window.loadingFullPage();

                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: message,
                })
            }

            async function getLatLong(callback) {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        callback({
                            lat: position.coords.latitude,
                            long: position.coords.longitude
                        });
                    }, showError);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Geolocation is not supported by this browser.',
                    })
                }
            }

            $(document).on('click', '#checkin-btn', async function () {
                window.loadingFullPage();
                await getLatLong(async function (locationData) {
                    let dataCheckin = locationData;
                    dataCheckin.store_id = '{{ $id }}' ?? null;

                    await ajax(ROUTE_CHECKIN_STORE, 'POST', dataCheckin)
                        .then(async function (response) {
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function () {
                                window.location.reload();
                            })
                        }).catch(function (error) {
                            window.loadingFullPage();
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: error.responseJSON.message,
                            })
                        })
                }.bind(this))
            });


            $(document).on('click', '.checkout-btn', async function () {
                window.loadingFullPage();
                await getLatLong(async function (locationData) {
                    let dataCheckin = locationData;
                    dataCheckin.store_id = $(this).attr('store-id');

                    await ajax(ROUTE_CHECKOUT_STORE, 'POST', dataCheckin)
                        .then(async function (response) {
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function () {
                                window.location.reload();
                            })
                        }).catch(function (error) {
                            window.loadingFullPage();
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: error.responseJSON.message,
                            })
                        })
                }.bind(this));
            })
        });
    </script>
@endpush
