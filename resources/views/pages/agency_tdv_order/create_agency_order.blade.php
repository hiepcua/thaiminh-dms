@extends('layouts.main')
@section('page_title', $page_title)
@section('content')
    <section class="app-user-list">
        <div class="row" id="table-striped">
            <div class="col-12">
                <div class="card">
                    <form class="card-body" method="post" action="{{ route('admin.agency-order-tdv.create-agency-order') }}">
                        @csrf
                    @foreach($agencyStoreOrders as $key => $agencyStoreOrder)
                        <div class="mt-2"><b>Đại lý:</b> {{ $agencyStoreOrder['agency']->name }}</div>
                        <div><b>Địa bàn:</b> {{ $agencyStoreOrder['agency']->localiesName }}</div>
                        <div><b>Địa chỉ:</b> {{ $agencyStoreOrder['agency']->address }}</div>

                        <div class="mt-1">
                            <table class="table table-striped table-bordered">
                                <tr>
                                    <th class="text-center">Ngày lên đơn</th>
                                    <th class="text-center">Trình dược viên</th>
                                    <th class="text-center">Sản phẩm</th>
                                    <th class="text-center" style="width: 200px">Chiết khấu</th>
                                    <th class="text-center" style="width: 200px">Tổng tiền</th>
                                </tr>
                                @foreach($agencyStoreOrder['orders'] as $order)
                                    <input name="storeOrder[]" value="{{ $order->id }}" class="d-none">
                                    <tr>
                                        <td class="text-center">{{ $order->booking_at }}</td>
                                        <td class="text-center">{{ $order->sale?->name }}</td>
                                        <td class="text-nowrap">
                                            @foreach($order->items as $item)
                                                <div class="w-100 d-flex">
                                                    <div>
                                                    {{ $item->product_name }}
                                                    </div>
                                                    <div class="ms-auto">
                                                        {{ $item->product_qty }} * {!! \App\Helpers\Helper::formatPrice($item->product_price) !!}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </td>
                                        <td style="text-align: right">{!! $order->discount ? \App\Helpers\Helper::formatPrice($order->discount) : 0 !!}</td>
                                        <td style="text-align: right">{!! \App\Helpers\Helper::formatPrice($order->total_amount) !!}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @endforeach
                        <div class="mt-1 d-flex align-items-center">
                            <label class="form-label" for="form-status">
                                Ngày sinh mã đơn<span class="text-danger">(*)</span>
                            </label>
                            <input name="booking_at" class="form-control flatpickr-basic flatpickr-input ms-1" style="width: 200px" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="mt-1">
                            <button type="submit" class="btn btn-primary">Thanh toán</button>
                            <a href="{{ route('admin.agency-order-tdv.index') }}" class="btn btn-secondary">Trở lại</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
