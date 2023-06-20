<div style="margin-top: 0.5rem;margin-bottom: 0.5rem;">
    <table class="table table-bordered">
        <tr>
            <td colspan="4" class="bg-{{ $item->status == 2 ? 'success' : 'warning' }}">
                <div class="d-flex">
                    <span class="text-white fw-bolder">
                        {{ $item->status_name }} {{ $item->delivery_at ?? '' }}
                    </span>
                    <span class="ms-auto text-white fst-italic">{{ $item->code }}</span>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <div class="d-flex">
                    <span class="fw-bolder">{!! $item->store_name !!}</span>
                    <span class="ms-auto">{{ $item->store_code }}</span>
                </div>
            </td>
        </tr>
        <tr>
            <td class="text-center" style="width: 35%;">
                Ngày nhập
                <br>
                <span class="fw-bolder fst-italic">{{ $item->booking_at }}</span>
            </td>
            <td colspan="3" style="vertical-align: bottom">
                @if ($item->discount)
                    <div class="d-flex">
                        <span class="fw-bold">Chiết khấu:</span>
                        <span class="ms-auto fw-bold">{!! $item->col_discount_mobile  !!}</span>
                    </div>
                @endif
                <div class="d-flex">
                    <span class="fw-bolder">Tổng tiền:</span>
                    <span class="ms-auto fw-bolder">{{ $item->col_total_amount }}</span>
                </div>
            </td>
        </tr>
        <tr>
            <th>Sản phẩm</th>
            <th class="text-end">SL</th>
            <th class="text-end">Giá</th>
            <th class="text-end">Thành tiền</th>
        </tr>
        @foreach ($item->list_product as $_product)
            <tr>
                <td>{!! $_product['type'] == 'gift' ? '<i data-feather="gift" class="text-success feather-gift-fix"></i>' : '' !!}  {{ $_product['code'] }}</td>
                <td class="text-end">{{ $_product['qty'] }}</td>
                <td class="text-end">{{ $_product['price_format'] ?? '0' }}</td>
                <td class="text-end">{{ $_product['amount_format'] }}</td>
            </tr>
        @endforeach
        @if($item->note)
            <tr>
                <td colspan="4">{{ $item->note }}</td>
            </tr>
        @endif
    </table>
</div>
