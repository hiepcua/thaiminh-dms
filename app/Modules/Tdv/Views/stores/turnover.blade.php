<?php

use \Illuminate\Support\Carbon;

?>
@extends('layouts.main')
@section('content')
    <div class="row match-height">
        <div class="col-12">
            <div class="card card-statistics">
                <div class="card-body">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" id="home-tab" data-bs-toggle="pill" href="#home"
                               aria-expanded="true">
                                Tháng {{ $data['currentMonth'] ?? '' }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab" data-bs-toggle="pill" href="#profile"
                               aria-expanded="false">
                                Chu kỳ {{ Carbon::create($data['currentRevenue']['from'] ?? '')->format('Y-m') }}
                                - {{ Carbon::create($data['currentRevenue']['to'] ?? '')->format('Y-m') }}
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="home" aria-labelledby="home-tab"
                             aria-expanded="true">
                            @php($monthTotalAmount = $data['month']['totalAmount'] ?? [])
                            @php($groupsAllUser = $data['month']['groupsAllUser'] ?? [])
                            @php($groupsCurrentUser = $data['month']['groups'] ?? [])
                            <p class="mb-0 mt-1" style="border-right: unset; color: #37ab6c">
                                <b>Doanh thu: {!! Helper::formatPrice($monthTotalAmount['current'] ?? '', 'đ') !!}</b>
                            </p>
                            <p>{!! $monthTotalAmount['percent'] ?? '' !!}
                                Tháng {{ $monthTotalAmount['previousMonth'] ?? '' }}
                                ({!! Helper::formatPrice($monthTotalAmount['previous'] ?? '', 'đ')  !!})</p>
                            @if(count($groupsAllUser))
                                <p class="mb-0 mt-2" style="border-right: unset; color: #37ab6c">
                                    <b>Doanh thu theo nhóm SP (Theo địa bàn)</b>
                                </p>
                                @foreach($groupsAllUser as $groupName => $group)
                                    <p class="mb-0">
                                        <b>{{ $groupName }}: </b>
                                        {!! Helper::formatPrice($group['month_total_amount'] ?? '', 'đ') !!}
                                    </p>
                                @endforeach

                                <p class="mb-0 mt-2" style="border-right: unset; color: #37ab6c">
                                    <b>Doanh thu theo nhóm SP (Cá nhân)</b>
                                </p>
                                @foreach($groupsCurrentUser as $groupName => $group)
                                    <p class="mb-0" style="border-right: unset; margin-top: 5px">
                                        <b>{{ $groupName }}</b>
                                    </p>
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th class="text-center" style="width: 180px;"><b>Tên</b></th>
                                            <th class="text-center"><b>Doanh thu</b></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($group['items'] as $product)
                                            <tr>
                                                <td>{{ $product['name'] ?? '' }}</td>
                                                <td class="text-end">
                                                    {!! \App\Helpers\Helper::formatPrice($product['month_total_amount'] ?? '')  !!}
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        <tfoot>
                                        <tr class="row-total table-light">
                                            <td><b>Tổng</b></td>
                                            <td class="text-end">
                                                {!! \App\Helpers\Helper::formatPrice($group['month_total_amount'])  !!}
                                            </td>
                                        </tr>
                                        </tfoot>
                                    </table>
                                @endforeach
                            @endif
                        </div>
                        <div class="tab-pane" id="profile" role="tabpanel" aria-labelledby="profile-tab"
                             aria-expanded="false">
                            @php($revenueTotalAmount = $data['revenue']['totalAmount'] ?? [])
                            @php($revenueGroupsAllUser = $data['revenue']['groupsAllUser'] ?? [])
                            @php($revenueGroupsCurrentUser = $data['revenue']['groups'] ?? [])
                            <p class="mb-0 mt-1" style="border-right: unset; color: #37ab6c">
                                <b>Doanh thu: {!! Helper::formatPrice($revenueTotalAmount['current'] ?? '', 'đ') !!}</b>
                            </p>
                            <p>{!! $revenueTotalAmount['percent'] ?? '' !!} Chu
                                kỳ {{ $revenueTotalAmount['previousRevenue'] ?? '' }}
                                ({!! Helper::formatPrice($revenueTotalAmount['previous'] ?? '', 'đ')  !!})</p>
                            @if(count($groupsAllUser))
                                <p class="mb-0 mt-2" style="border-right: unset; color: #37ab6c">
                                    <b>Doanh thu theo nhóm SP (Theo địa bàn)</b>
                                </p>
                                @foreach($revenueGroupsAllUser as $groupName => $group)
                                    <p class="mb-0">
                                        <b>{{ $groupName }}: </b>
                                        {!! Helper::formatPrice($group['month_total_amount'] ?? '', 'đ') !!}
                                    </p>
                                @endforeach

                                <p class="mb-0 mt-2" style="border-right: unset; color: #37ab6c">
                                    <b>Doanh thu theo nhóm SP (Cá nhân)</b>
                                </p>
                                @foreach($revenueGroupsCurrentUser as $groupName => $group)
                                    <p class="mb-0" style="border-right: unset; margin-top: 5px">
                                        <b>{{ $groupName }}</b>
                                    </p>
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th class="text-center" style="width: 180px;"><b>Tên</b></th>
                                            <th class="text-center"><b>Doanh thu</b></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($group['items'] as $product)
                                            <tr>
                                                <td>{{ $product['name'] ?? '' }}</td>
                                                <td class="text-end">
                                                    {!! \App\Helpers\Helper::formatPrice($product['month_total_amount'] ?? '')  !!}
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        <tfoot>
                                        <tr class="row-total table-light">
                                            <td><b>Tổng</b></td>
                                            <td class="text-end">
                                                {!! \App\Helpers\Helper::formatPrice($group['month_total_amount'])  !!}
                                            </td>
                                        </tr>
                                        </tfoot>
                                    </table>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
