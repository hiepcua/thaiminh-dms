@extends('layouts.main')
@section('content')
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <span class="badge bg-success rounded-3" id="refresh-store-list" style="padding: 5px 10px; cursor: pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-refresh" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"></path>
                            <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"></path>
                        </svg>
                        refresh
                    </span>
                    <table class="table mt-2">
                        <thead>
                            <tr>
                                <th class="text-center">Tên</th>
{{--                                <th class="text-center">Khoảng cách(m)</th>--}}
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="table_content">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('component.loadingFull')

@endsection
@push('scripts-custom')
    <script>
        $(document).ready(function () {
            const ROUTE_GET_LIST_STORE = '{{ route('admin.tdv.checkin.get-list-store') }}';
            const ROUTE_CHECKIN_STORE = '{{ route('admin.tdv.checkin.checkin') }}';
            const ROUTE_CHECKOUT_STORE = '{{ route('admin.tdv.checkin.checkout') }}';
            window.currentLocation = null;

            function reloadTableBody(data)
            {
                let tableContent = $('#table_content');
                tableContent.html('');
                data.forEach(function (row) {
                    let rowContent = `<tr>
                    <td>
                        <b>${row.name}</b> <br>
                        <b>KC:</b> ${row.distance}m
                    </td>
                    <td class="text-center">${row.action}</td>
                </tr>`;

                    tableContent.append(rowContent);
                })
            }

            function showError(error) {
                let message = '';
                switch(error.code) {
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

            async function getLatLong(callback)
            {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        callback({
                            lat: position.coords.latitude,
                            long: position.coords.longitude
                        });
                    },showError);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Geolocation is not supported by this browser.',
                    })
                }
            }

            async function getListStore()
            {
                // let locationData = await getLatLong();
                await getLatLong(async function (locationData) {
                    if(locationData) {
                        await ajax(ROUTE_GET_LIST_STORE, 'POST', locationData)
                        .then(function (response) {
                            let stores = response.stores;
                            reloadTableBody(stores);
                            window.loadingFullPage();
                        }).catch(function (error) {
                            window.loadingFullPage();
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: "Có lỗi xảy ra. Vui lòng thử lại sau!",
                            })
                        })
                    }
                });
            }

            async function reloadListStore() {
                window.loadingFullPage();

                await getListStore();
            }

            reloadListStore();

            $("#refresh-store-list").on('click', function () {
                reloadListStore();
            })

            $(document).on('click', '.checkin-btn', async function () {
                window.loadingFullPage();
                await getLatLong(async function (locationData) {
                    let dataCheckin = locationData;
                    dataCheckin.store_id = $(this).attr('store-id');

                    await ajax(ROUTE_CHECKIN_STORE, 'POST', dataCheckin)
                        .then(async function (response) {
                            await getListStore();
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
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

            $(document).on('click', '.checkout-btn', async function () {
                window.loadingFullPage();
                await getLatLong(async function (locationData) {
                    let dataCheckin = locationData;
                    dataCheckin.store_id = $(this).attr('store-id');

                    await ajax(ROUTE_CHECKOUT_STORE, 'POST', dataCheckin)
                        .then(async function (response) {
                            await getListStore();
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
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
        })

    </script>
@endpush
