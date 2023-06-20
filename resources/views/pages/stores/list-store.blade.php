<table id="table-listStore" class="table mt-2">
    @if ($result)
        <thead>
        <tr>
            <th>STT</th>
            <th>MÃ NT</th>
            <th>TÊN</th>
            <th>ĐỊA CHỈ</th>
            <th>HUYỆN/TỈNH</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($result as $key => $item)
            <tr>
                <td>{{ ($key + 1) }}</td>
                <td>{{ $item->code }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->address }}</td>
                <td>{{ $item->district_name }}</td>
                <td><a href="javascript:void(0)" class="btn btn-primary model-selectStore"
                       data-storeId="{{ $item->id }}"
                       data-storeCode="{{ $item->code }}"
                       data-storeName="{{ $item->name }}"
                    ><i data-feather='arrow-down-circle'></i></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    @else
        <tr>
            <td colspan="6">Không có dữ liệu</td>
        </tr>
    @endif
</table>
