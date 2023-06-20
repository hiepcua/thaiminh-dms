<table>
    <thead>
    <tr>
        <th colspan="9">
            {{$headers}}
        </th>
    </tr>
    <tr>
        <th style="text-align: center" rowspan="2">STT</th>
        <th style="text-align: center" rowspan="2">Tên nhà thuốc</th>
        <th style="text-align: center" rowspan="2">Địa chỉ</th>
        <th style="text-align: center" rowspan="2">Số điện thoại</th>
        <th style="text-align: center" colspan="2">Kích thước poster</th>
        <th style="text-align: center" rowspan="2">Diện tích</th>
        <th style="text-align: center" rowspan="2">Ghi chí</th>
        <th style="text-align: center" rowspan="2">Trả thưởng</th>
    </tr>
    <tr>
        <th style="text-align: center">Ngang (cm)</th>
        <th style="text-align: center">Cao (cm)</th>
    </tr>
    </thead>
    <tbody>
    {{$i = 1}}
    @foreach($datas as $item)
        <tr>
            <td>{{ $i++ }}</td>
            <td>{{ $item->pharmacy_name }}</td>
            <td>{{ $item->pharmacy_address }}</td>
            <td style="text-align: right">{{ $item->pharmacy_phone }}</td>
            <td style="text-align: right">{{ $item->poster_width }}</td>
            <td style="text-align: right">{{ $item->poster_height }}</td>
            <td style="text-align: right">{{ $item->poster_area }}</td>
            <td style="text-align: right">{{ $item->note }}</td>
            <td></td>
        </tr>
    @endforeach
    </tbody>
</table>
