<?php
use App\Models\AgencyOrderItem;
?>
@extends('layouts.main')
@section('page_title', $pageTitle)
@section('content')
    <section class="app-user-list">
        <div class="row" id="table-striped">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div><b>Đại lý:</b> {{ $agencyOrder['agencyName'] ?? '' }}</div>
                        <div><b>Địa bàn:</b> {{ $agencyOrder['localities'] ?? '' }}</div>
                        <div><b>Địa chỉ:</b> {{ $agencyOrder['agencyAddress'] ?? '' }}</div>
                        <div><b>Mã số đơn TT:</b>
                            <a href="{{ route('admin.agency-order-tdv.index', [
                                'search' => [
                                        'agencyCode' => $agencyOrder['agencyOrderCode'] ?? '',
                                        'agency_status' => \App\Models\StoreOrder::AGENCY_STATUS_DA_THANH_TOAN
                                    ]
                                ]) }}">
                                {{ $agencyOrder['agencyOrderCode'] ?? '' }}
                            </a>
                        </div>
                        <div><b>Ngày TT:</b> {{ $agencyOrder['bookingAt'] ?? '' }}</div>

                        <div class="mt-1">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center">Sản phẩm</th>
                                        <th class="text-center">Số lượng</th>
                                        <th class="text-center">Đơn giá</th>
                                        <th class="text-center">Thành tiền</th>
                                        <th class="text-center">Chiết khấu</th>
                                        <th class="text-center">Tiền thanh toán</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @php($totalOriginalAmount = 0)
                                @php($totalFinalAmount = 0)
                                @php($totalDiscount = 0)
                                @php($totalQty = 0)
                                @foreach($agencyOrder['items'] as $item)
                                    <?php
                                        if ($item['product_type'] == AgencyOrderItem::PRODUCT_TYPE_PRODUCT) {
                                            $totalOriginalAmount += $item['sub_total'] ?? 0;
                                            $totalFinalAmount += $item['total_amount'] ?? 0;
                                            $totalDiscount += $item['discount'];
                                            $totalFinalAmount -= $item['discount'];
                                        }

                                        $totalQty += $item['product_qty'] ?? 0;
                                    ?>
                                    <tr>
                                        <td>{{ $item['product_name'] ?? '' }}</td>
                                        <td class="text-end">{{ $item['product_qty'] ?? '' }}</td>
                                        <td style="text-align: right">
                                            {!! $item['product_type'] == AgencyOrderItem::PRODUCT_TYPE_PRODUCT ? \App\Helpers\Helper::formatPrice($item['product_price']) : 0 !!}
                                        </td>
                                        <td style="text-align: right">
                                            {!! $item['product_type'] == AgencyOrderItem::PRODUCT_TYPE_PRODUCT ? \App\Helpers\Helper::formatPrice($item['sub_total']) : 0 !!}
                                        </td>
                                        <td style="text-align: right">
                                            {!! \App\Helpers\Helper::formatPrice($item['discount']) !!}
                                        </td>
                                        <td style="text-align: right">{!! ($item['product_type'] == AgencyOrderItem::PRODUCT_TYPE_DISCOUNT ? '-' : '') . \App\Helpers\Helper::formatPrice($item['total_amount']) !!}</td>
                                    </tr>
                                @endforeach
                                    <tr style="background-color: #f3f2f7">
                                        <td class="text-center"><b>Tổng</b></td>
                                        <td class="text-end"><b>{{ $totalQty }}</b></td>
                                        <td></td>
                                        <td class="text-end"><b>{!! \App\Helpers\Helper::formatPrice($totalOriginalAmount) !!}</b></td>
                                        <td class="text-end"><b>{!! \App\Helpers\Helper::formatPrice($totalDiscount) !!}</b></td>
                                        <td class="text-end"><b>{!! \App\Helpers\Helper::formatPrice($totalFinalAmount) !!}</td></b></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
