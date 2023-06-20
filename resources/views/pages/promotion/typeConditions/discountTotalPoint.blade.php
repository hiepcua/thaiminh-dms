<?php
use \App\Models\PromotionCondition;
use \App\Models\Promotion;
?>

{{--Chiết khấu tổng điểm--}}

<div class="wrap-repeater-promotion mt-1 inner-repeater template-type @if($typeDefault != 'type' . PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT) d-none @endif"
     data-template-type="type{{ PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT }}">
    <div data-repeater-list="type{{PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT}}" style="position: relative" class="border-secondary rounded">
        @if(count($infoTypeCondition['type' . PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT] ?? []))
{{--Edit screen--}}
            @foreach($infoTypeCondition['type' . PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT] as $condition)
                @php($typeInclude = $condition['typeInclude'])
                @php($includes = $condition['includes'])
                @php($typeExclude = $condition['typeExclude'])
                @php($excludes = $condition['excludes'])
            <div data-repeater-item class="p-1 border-bottom">
            <!-- innner repeater -->
                <div class="p-1 condition-type{{PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT}}">
                    <div class="row">
                        <label class="col-xl-2 col-md-2 col-sm-4">SP áp dụng<span class="text-danger">(*)</span></label>
                        <label class="col-xl-10 col-md-10 col-sm-8 d-flex mb-1">
                            <select class="form-select me-1 select-type-include" name="includeType" style="min-width: 200px; width: 200px">
                                <option value="" @if($typeInclude === '') selected @endif>Tất cả</option>
                                <option value="include-products" @if($typeInclude === 'include-products') selected @endif>Sản phẩm được chọn</option>
                                <option @if($typeInclude === 'include-groups') selected @endif value="include-groups">Nhóm sản phẩm</option>
                            </select>
                            <div class="w-100 include-product include-products">
                                <select class="has-select2 form-select select-product include-product" for-type="type{{PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT}}"
                                        multiple name="includeProducts">
                                    <option value="all">Tất cả</option>
                                    @foreach($formOptions['products'] ?? [] as $key => $product)
                                        <option value="{{ $key }}" @if(in_array($key, $includes) && $typeInclude === 'include-products') selected @endif>
                                            {{ $product }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-100 d-none include-product include-groups">
                                <select class="has-select2 form-select select-product include-product" for-type="type{{PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT}}"
                                        multiple name="includeGroups">
                                    <option value="all">Tất cả</option>
                                    @foreach($formOptions['productGroups'] ?? [] as $key => $productGroup)
                                        <option value="{{ $key }}" @if(in_array($key, $includes) && $typeInclude === 'include-groups') selected @endif>
                                            {{ $productGroup }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </label>
                        <label class="col-xl-2 col-md-2 col-sm-4">SP loại trừ<span class="text-danger">(*)</span></label>
                        <label class="col-xl-10 col-md-10 col-sm-8 d-flex">
                            <select class="form-select me-1 select-type-exclude"
                                    style="min-width: 200px; width: 200px"
                                    for-type="type{{PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT}}"
                                    name="excludeType"
                            >
                                <option value="" @if($typeExclude === '') selected @endif>Không loại trừ</option>
                                <option value="exclude-groups" @if($typeExclude === 'exclude-groups') selected @endif>Nhóm sản phẩm</option>
                                <option value="exclude-products" @if($typeExclude === 'exclude-products') selected @endif>Sản phẩm được chọn</option>
                            </select>
                            <div class="w-100 d-none exclude-product exclude-products">
                                <select class="has-select2 form-select select-product" for-type="type{{PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT}}" multiple name="excludeProducts">
                                    <option value="all">Tất cả</option>
                                    @foreach($formOptions['products'] ?? [] as $key => $product)
                                        <option value="{{ $key }}" @if(in_array($key, $excludes) && $typeExclude === 'exclude-products') selected @endif>
                                            {{ $product }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-100 d-none exclude-product exclude-groups">
                                <select class="has-select2 form-select select-product" for-type="type{{PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT}}" multiple name="excludeGroups">
                                    <option value="all">Tất cả</option>
                                    @foreach($formOptions['productGroups'] ?? [] as $key => $productGroup)
                                        <option value="{{ $key }}" @if(in_array($key, $excludes) && $typeExclude === 'exclude-groups') selected @endif>
                                            {{ $productGroup }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </label>
                        <hr class="mt-1 mb-1">
                        <div class="mb-1">Khuyến mãi<span class="text-danger">(*)</span></div>
                        <div class="deep-inner-repeater">
                            <table class="table table-striped table-bordered mb-1 sub-condition" data-repeater-list="gifts">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 200px">Số điểm tối thiểu</th>
                                    <th class="text-center" style="width: 200px">Loại SP tối thiểu</th>
                                    <th class="text-center">Lượng giảm</th>
                                    <th class="text-center" style="width: 200px">Loại Chiết khấu</th>
                                    <th class="text-center">Lượng giảm tối đa</th>
                                    <th style="width: 100px"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($condition['conditions'] as $conditionGift)
                                    <tr data-repeater-item class="condition-row">
                                        <td>
                                            <input type="number" class="form-control" name="minPoint" value="{{ $conditionGift['minPoint'] ?? null }}" min="1"/>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control min-type" name="minType"
                                                   max="{{ count($formOptions['products'] ?? []) }}" value="{{ $conditionGift['minType'] ?? null }}" min="1"/>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="discount" value="{{ $conditionGift['discount'] ?? null }}" min="1"/>
                                        </td>
                                        <td>
                                            <select name="type" class="form-select type-discount">
                                                <option value="{{ PromotionCondition::TYPE_DISCOUNT_VND }}"
                                                        @if(($conditionGift['type'] ?? null) == PromotionCondition::TYPE_DISCOUNT_VND) selected @endif>
                                                    {{ PromotionCondition::TYPE_DISCOUNT_TEXTS[PromotionCondition::TYPE_DISCOUNT_VND] }}
                                                </option>
                                                <option value="{{ PromotionCondition::TYPE_DISCOUNT_PERCENT }}"
                                                        @if(($conditionGift['type'] ?? null) == PromotionCondition::TYPE_DISCOUNT_PERCENT) selected @endif>
                                                    {{ PromotionCondition::TYPE_DISCOUNT_TEXTS[PromotionCondition::TYPE_DISCOUNT_PERCENT] }}
                                                </option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control max-discount @if(($conditionGift['type'] ?? null) != PromotionCondition::TYPE_DISCOUNT_PERCENT) d-none @endif"
                                               name="limitDiscount" value="{{ $conditionGift['limitDiscount'] ?? null }}" min="1"/>
                                        </td>
                                        <td>
                                            <center>
                                                <input data-repeater-delete type="button" value="Xóa" class="btn btn-secondary"/>
                                            </center>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <input class="btn btn-outline-success me-1 btn-add-new-sub-condition"
                                   data-repeater-create type="button" value="Thêm khuyến mãi"/>
                            <input data-repeater-delete type="button" value="Xóa điều kiện" class="btn btn-secondary" style="position: absolute"/>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @else
{{--Create screen--}}
            <div data-repeater-item class="p-1 border-bottom">
                <!-- innner repeater -->
                <div class="p-1 condition-type{{PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT}}">
                    <div class="row">
                        <label class="col-xl-2 col-md-2 col-sm-4">SP áp dụng<span class="text-danger">(*)</span></label>
                        <label class="col-xl-10 col-md-10 col-sm-8 d-flex mb-1">
                            <select class="form-select me-1 select-type-include" name="includeType" style="min-width: 200px; width: 200px">
                                <option value="" selected>Tất cả</option>
                                <option value="include-products">Sản phẩm được chọn</option>
                                <option value="include-groups">Nhóm sản phẩm</option>
                            </select>
                            <div class="w-100 include-product d-none include-products">
                                <select class="has-select2 form-select select-product include-product" for-type="type{{PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT}}"
                                        multiple name="includeProducts">
                                    <option value="all">Tất cả</option>
                                    @foreach($formOptions['productGroups'] ?? [] as $key => $productGroup)
                                        <option value="{{ $key }}">
                                            {{ $productGroup }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-100 d-none include-product include-groups">
                                <select class="has-select2 form-select select-product include-product" for-type="type{{PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT}}"
                                        multiple name="includeGroups">
                                    <option value="all">Tất cả</option>
                                    @foreach($formOptions['products'] ?? [] as $key => $product)
                                        <option value="{{ $key }}">
                                            {{ $product }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </label>
                        <label class="col-xl-2 col-md-2 col-sm-4">SP loại trừ<span class="text-danger">(*)</span></label>
                        <label class="col-xl-10 col-md-10 col-sm-8 d-flex">
                            <select class="form-select me-1 select-type-exclude"
                                    style="min-width: 200px; width: 200px"
                                    name="excludeType"
                                    for-type="type{{PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT}}"
                            >
                                <option value="" selected>Không loại trừ</option>
                                <option value="exclude-groups">Nhóm sản phẩm</option>
                                <option value="exclude-products">Sản phẩm được chọn</option>
                            </select>
                            <div class="w-100 d-none exclude-product exclude-products">
                                <select class="has-select2 form-select select-product" for-type="type{{PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT}}" multiple name="excludeProducts">
                                    <option value="all">Tất cả</option>
                                    @foreach($formOptions['products'] ?? [] as $key => $product)
                                        <option value="{{ $key }}">
                                            {{ $product }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-100 d-none exclude-product exclude-groups">
                                <select class="has-select2 form-select select-product" for-type="type{{PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT}}" multiple name="excludeGroups">
                                    <option value="all">Tất cả</option>
                                    @foreach($formOptions['productGroups'] ?? [] as $key => $productGroup)
                                        <option value="{{ $key }}">
                                            {{ $productGroup }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </label>
                        <hr class="mt-1 mb-1">
                        <div class="mb-1">Khuyến mãi<span class="text-danger">(*)</span></div>
                        <div class="deep-inner-repeater">
                            <table class="table table-striped table-bordered mb-1 sub-condition" data-repeater-list="gifts">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 200px">Số điểm tối thiểu</th>
                                    <th class="text-center" style="width: 200px">Loại SP tối thiểu</th>
                                    <th class="text-center">Lượng giảm</th>
                                    <th class="text-center" style="width: 200px">Loại Chiết khấu</th>
                                    <th class="text-center">Lượng giảm tối đa</th>
                                    <th style="width: 100px"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr data-repeater-item class="condition-row">
                                    <td>
                                        <input type="number" class="form-control" name="minPoint" min="1"/>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="minType" min="1"/>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="discount" min="1"/>
                                    </td>
                                    <td>
                                        <select name="type" class="form-select type-discount">
                                            <option value="{{ PromotionCondition::TYPE_DISCOUNT_VND }}" selected>
                                                {{ PromotionCondition::TYPE_DISCOUNT_TEXTS[PromotionCondition::TYPE_DISCOUNT_VND] }}
                                            </option>
                                            <option value="{{ PromotionCondition::TYPE_DISCOUNT_PERCENT }}">
                                                {{ PromotionCondition::TYPE_DISCOUNT_TEXTS[PromotionCondition::TYPE_DISCOUNT_PERCENT] }}
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control max-discount d-none" name="maxDiscount" min="1"/>
                                    </td>
                                    <td>
                                        <center>
                                            <input data-repeater-delete type="button" value="Xóa" class="btn btn-secondary"/>
                                        </center>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <input class="btn btn-outline-success me-1 btn-add-new-sub-condition"
                                   data-repeater-create type="button" value="Thêm khuyến mãi"/>
                            <input data-repeater-delete type="button" value="Xóa điều kiện" class="btn btn-secondary" style="position: absolute"/>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <input class="btn btn-outline-success mt-1 me-1 btn-add-new-condition" data-repeater-create type="button" value="Thêm điều kiện"/>
    <input data-repeater-delete type="button" value="Xóa Loại" class="btn btn-secondary mt-1" style="position: absolute"/>
</div>
