@foreach ($result as $key => $store)
    <tr id="store-locality-{{ $store->id }}" class="store-locality" onclick="checkedStore(this)"
        data-store-id="{{ $store->id }}">
        <td class="text-center">
            <div class="form-check">
                <input class="form-check-input chk-store" type="checkbox" name="free-store[]"
                       value="{{ $store->id }}" disabled>
            </div>
        </td>
        <td class="info">{{ $store->code . ' - ' . $store->name . ' - '. $store->address }}</td>
        <td></td>
    </tr>
@endforeach
