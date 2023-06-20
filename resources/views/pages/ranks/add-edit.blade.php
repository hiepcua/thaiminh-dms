@extends('layouts.main')
@section('page_title', $page_title)
@section('content')
    <div class="card">
        <div class="card-body">
            <form class="has-provinces" method="post" action="{{ $formOptions['action'] }}">
                @csrf
                @if($rank_id)
                    @method('put')
                @endif
                <div class="row mb-1">
                    <div class="col-6">
                        <label class="form-label" for="form-name">Tên hạng <span class="text-danger">(*)</span></label>
                        <input type="text" id="form-name" class="form-control" name="name" value="{{ $default_values['name'] ?? '' }}" placeholder="Tên hạng" required>
                    </div>

                    <div class="col-6">
                        <label class="form-label" for="form-status">Trạng thái</label>
                        <select id="form-status" class="form-control" name="status">
                            @foreach(\App\Models\Rank::STATUS_TEXT as $v => $n)
                                <option
                                    value="{{ $v }}" {{ $v === $default_values['status'] ? 'selected' : '' }}>
                                    {{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-1">
                    <div class="col-12">
                        <label class="form-label" for="form-desc">Mô tả</label>
                        <textarea id="form-desc" class="form-control" name="desc" rows="3">{{ $default_values['desc'] ?? '' }}</textarea>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success me-1">
                        {{ $rank_id ? 'Cập nhật' : 'Tạo mới' }}
                    </button>

                    <a href="{{ route('admin.ranks.index') }}" class="btn btn-secondary me-1"><i data-feather='rotate-ccw'></i> Quay lại</a>
                </div>
            </form>
        </div>
    </div>
@endsection
