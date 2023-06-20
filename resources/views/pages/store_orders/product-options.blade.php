<option value="">- Tìm sản phẩm -</option>
@foreach($products as $_product)
    <option value="{{ $_product->id }}"
            data-name="{{ $_product->name }}"
            data-price="{{ $_product->wholesale_price }}"
            data-point="{{ $_product->point }}"
            data-type="{{ $_product->point }}"
    >
        {{ $_product->name }}
    </option>
@endforeach
