@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body">


            <form class="form form-vertical  col-sm-12 " method="post" action="{{ $formOptions['action'] }}">

                @csrf
                @if($product_group_id)
                    @method('put')
                @endif
                <div class="row">
                    <div class="col-12 col-md-6 col-sm-12">
                        <div class="row">
                            <div class="col-12 col-sm-12">
                                <div class="mb-1 ">
                                <label class="form-label" for="form-parent_id">Loại hàng</label>
                                <select id="form-parent_" class="form-control" required
                                        name="product_type">
                                    <option value="">Chọn loại sản phẩm</option>
                                    @foreach($productTypes as $_id => $_value)
                                        <option @if($default_values['product_type'] == $_id) selected @endif value="{{ $_id }}" >{{ @$_value['text'] }}</option>
                                    @endforeach
                                </select>
                                </div>
                            </div>

                            <div class="col-12 col-sm-12">
                                <div class="mb-1 ">
                                    <label class="form-label" for="form-name">Tên</label>
                                    <input type="text" id="form-name"
                                           class="form-control"
                                           name="name"
                                           value="{{ $default_values['name'] }}" required/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-12 col-sm-12"></div>
                    <div class="col-12 col-sm-6">
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="mb-1">
                                    <label class="form-label" for="form-parent_id">Cấp cha</label>
                                    <select id="form-parent_id" class="form-control"
                                            name="parent_id" {{ request()->route()->getName() == 'admin.product-groups.edit' ? 'disabled':'' }}>
                                        <option value="0"> Chọn cấp cha </option>
                                        @foreach($formOptions['parents'] as $item_parent)
                                            <option
                                                value="{{ $item_parent->id }}" {{ $item_parent->id == $default_values['parent_id'] ? 'selected' : '' }}>
                                                {{ $item_parent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="mb-1">
                                    <label class="form-label" for="form-status">Trạng thái</label>
                                    <select id="form-status" class="form-control" name="status">
                                        @foreach($formOptions['status'] as $v => $n)
                                            <option
                                                value="{{ $v }}" {{ $v === $default_values['status'] ? 'selected' : '' }}>
                                                {{ $n }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12">
                                <div class="mb-1">
                                    <label class="form-label" for="form-note">Mô tả</label>
                                    <textarea class="form-control" name="note">{{ $default_values['note'] }}</textarea>
                                </div>
                                {{--                        <input type="text" id="form-note" class="form-control"--}}
                                {{--                               name="note" value="{{ $default_values['note'] }}">--}}
                            </div>

                        </div>
                    </div>

                    <div class="col-sm-12" >
                        <div class="row"><div class="col-sm-12">
                                <button type="submit" class="btn btn-success me-1">
                                    {{ $product_group_id ? 'Cập nhật' : 'Tạo mới' }}
                                </button>
                                <a href="{{ route('admin.product-groups.index') }}" class="btn btn-secondary me-1"><i
                                        data-feather='rotate-ccw'></i> Quay lại</a>
                            </div>
                        </div>
                    </div>




                </div>

            </form>
        </div>
    </div>
@endsection
