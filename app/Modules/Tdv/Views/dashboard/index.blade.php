@extends('layouts.main')
@section('content')
    <div class="row match-height">
        <div class="col-12">
            <div class="card card-statistics">
                <div class="card-body">
                    {!!
                        \App\Helpers\SearchFormHelper::getForm(
                            route('admin.tdv.dashboard'),
                            'GET',
                            [
                                [
                                    "type" => "selection",
                                    "name" => "search[month]",
                                    "defaultValue" => $formOption['default']['month'] ?? '',
                                    "options" => $formOption['months'],
                                    "id" => "form-locality_ids",
                                    "class" => 'col-2'
                                ],
                                [
                                    "type" => "selection",
                                    "name" => "search[year]",
                                    "defaultValue" => $formOption['default']['year'] ?? '',
                                    "options" => $formOption['years'],
                                    "id" => "form-locality_ids",
                                    "class" => 'col-4'
                                ],
                            ],
                        )
                    !!}
                    <p class="mb-0 mt-1" style="border-right: unset; color: #37ab6c">
                        <b>Doanh thu: {!! Helper::formatPrice($data['totalAmountCurrentMonth'] ?? '') !!}đ</b>
                    </p>
                    <p>{!! $data['percentAgoMonthText'] ?? '' !!}</p>
                    @if(count($data['orders']))
                        <p class="mb-0 mt-2" style="border-right: unset; color: #37ab6c">
                            <b>Doanh thu theo nhóm SP (Theo địa bàn)</b>
                        </p>
                        @foreach($data['totalAmountByGroup'] ?? [] as $totalAmountGroup)
                            <p class="mb-0">
                                <b>{{ $totalAmountGroup->productGroup?->name }}: </b>
                                {!! Helper::formatPrice($totalAmountGroup->month_total_amount ?? '') !!}đ
                            </p>
                        @endforeach

                        <p class="mb-0 mt-2" style="border-right: unset; color: #37ab6c">
                            <b>Doanh thu theo nhóm SP (Cá nhân)</b>
                        </p>
                        @foreach($data['orders'] as $key => $order)
                            <p class="mb-0" style="border-right: unset; margin-top: 5px">
                                <b>{{ $key }}</b>
                            </p>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 180px;"><b>Tên</b></th>
                                        <th class="text-center"><b>DT</b></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order['item'] as $product)
                                    <tr>
                                        <td>{{ $product['name'] ?? '' }}</td>
                                        <td class="text-end">
                                            {!! \App\Helpers\Helper::formatPrice($product['month_total_amount'] ?? '') !!}đ
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="row-total table-light">
                                        <td><b>Tổng</b></td>
                                        <td class="text-end">
                                            {!! \App\Helpers\Helper::formatPrice($order['month_total_amount']) !!}đ
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
@endsection
