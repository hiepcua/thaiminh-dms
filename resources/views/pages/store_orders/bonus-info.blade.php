<table class="table table-borderless fw-bolder w-auto">
    @foreach($items as $value)
        @php
            $total =($value['bonus'] ?? 0);
        @endphp
        <tr>
            <td class="ps-0">Thưởng key {{ $value['product_type'] ?? '' }} {{ $value['product_name'] ?? '' }}:</td>
            <td class="text-end">
                {{ Helper::formatPrice($total) }}
            </td>
            <td><span data-total="{{$total}}" data-key="{{ $value['key'] }}" data-text="lệch"></span></td>
        </tr>
    @endforeach
    <tr>
        <td class="ps-0">Tổng thưởng key:</td>
        <td class="text-end">
            {{ Helper::formatPrice($total_bonus) }}
        </td>
        <td><span data-total="{{$total_bonus}}" data-key="key-total"></span></td>
    </tr>
</table>
