@if(count($result))
    <option value="">-- Chọn tuyến --</option>
    @foreach ($result as $key => $line)
        <option value="{{ $line->id }}">{{ $line->name }}</option>
    @endforeach
@else
    <option value="">-- Không có dữ liệu --</option>
@endif
