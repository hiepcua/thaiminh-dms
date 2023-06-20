<table>
    <thead>
        <tr>
            <th rowspan="2">Sản phẩm</th>
            <th rowspan="2">Giá tiền</th>
            @foreach($arrTdv as $tdvName)
                <th colspan="4">{{ $tdvName }}</th>
            @endforeach
            <th colspan="4">Tổng</th>
        </tr>
        <tr>
            @foreach($arrTdv as $tdv)
                <th>Số lượng</th>
                <th>Tổng tiền trước CK</th>
                <th>Chiết khấu</th>
                <th>Tổng tiền sau CK</th>
            @endforeach
            <th>Số lượng</th>
            <th>Tổng tiền trước CK</th>
            <th>Chiết khấu</th>
            <th>Tổng tiền sau CK</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $itemData)
            <tr>
                @foreach($itemData as $item)
                    <td>{{ $item }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
