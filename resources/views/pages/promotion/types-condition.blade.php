<?php
use \App\Models\PromotionCondition;
?>
<div class="wrap-repeater-promotion mt-1">
    <div data-repeater-list="typesPromotion">
        @if(isset($default_values['types']) && count($default_values['types']))
        @foreach($default_values['types'] ?? [] as $keyInfoTypeCondition => $infoTypeCondition)
        <div data-repeater-item class="p-1 promotion-setup">
            <div class="card type-promotion-repeater">
                <div  class="card-body">
                    <div class="row mb-1">
                        <div class="col-12">
                            <h4 for="form-code" class="d-flex align-items-center">
                                Loại<span class="text-danger">(*)</span>
                                @php($typeDefault = $infoTypeCondition['type'] ?? ("type" . PromotionCondition::TYPE_GIFT_BY_QTY))
                                <select name="type" class="form-select select-type-condition ms-1" style="width: 200px;">
                                    @foreach(PromotionCondition::TYPE_TEXTS as $key => $type)
                                        <option value="{{ $key }}" @if("type$key" == $typeDefault) selected @endif>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger ms-auto order-type-promotion-repeater"></span>
                            </h4>
                        </div>
                        <div class="d-flex align-items-center p-1">
                            <div class="justify-content-center me-1 text-nowrap">Tên điều kiện<span class="text-danger">(*)</span></div>
                            <div class="w-100">
                                <input class="form-control" name="typesPromotion[{{$keyInfoTypeCondition}}][nameCondition]" value="{{ $infoTypeCondition['nameCondition'] ?? '' }}"/>
                            </div>
                        </div>
                        <?php
                            $typeCondition = $infoTypeCondition['type'] ?? '';
                            $conditions = $infoTypeCondition[$typeCondition] ?? [];
                            foreach ($conditions as $key => $condition) {
                                $includeData = $condition['includes'] ?? [];
                                $typeInclude = $condition['includeType'] ?? null;
                                $includes = [];
                                $excludeData = $condition['excludes'] ?? [];
                                $typeExclude = $condition['excludeType'] ?? null;
                                $excludes = [];

                                if ($typeInclude) {
                                    if ($typeInclude == 'all') {
                                        $includes = [];
                                    }
                                    if ($typeInclude == 'include-groups') {
                                        $includes = $includeData['subGroups'];
                                    }
                                    if ($typeInclude == 'include-products') {
                                        $includes = $includeData['products'];
                                    }
                                } else {
                                    if (isset($includeData['all']) && $includeData['all']) {
                                        $typeInclude = 'all';
                                    }
                                    if (isset($includeData['subGroups']) && count($includeData['subGroups'])) {
                                        $typeInclude = 'include-groups';
                                        $includes = $includeData['subGroups'];
                                    }
                                    if (isset($includeData['products']) && count($includeData['products'])) {
                                        $typeInclude = 'include-products';
                                        $includes = $includeData['products'];
                                    }
                                }

                                if ($typeExclude) {
                                    if ($typeExclude == 'exclude-groups') {
                                        $excludes = $excludeData['subGroups'];
                                    }
                                    if ($typeExclude == 'exclude-products') {
                                        $excludes = $excludeData['products'];
                                    }
                                } else {
                                    if (isset($excludeData['subGroups']) && count($excludeData['subGroups'])) {
                                        $typeExclude = 'exclude-groups';
                                        $excludes = $excludeData['subGroups'];
                                    } else if (isset($excludeData['products']) && count($excludeData['products'])) {
                                        $typeExclude = 'exclude-products';
                                        $excludes = $excludeData['products'];
                                    } else {
                                        $typeExclude = 'none';
                                    }
                                }

                                $infoTypeCondition[$typeCondition][$key]['typeInclude'] = $typeInclude;
                                $infoTypeCondition[$typeCondition][$key]['includes'] = $includes;
                                $infoTypeCondition[$typeCondition][$key]['typeExclude'] = $typeExclude;
                                $infoTypeCondition[$typeCondition][$key]['excludes'] = $excludes;
                            }
                        ?>
                        @foreach (glob(base_path() . '/resources/views/pages/promotion/typeConditions/*.blade.php') as $file)
                            @include('pages.promotion.typeConditions.' . basename(str_replace('.blade.php', '', $file)),
                                [$infoTypeCondition]
                            )
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        @else
            <div data-repeater-item class="p-1 promotion-setup">
                <div class="card type-promotion-repeater">
                    <div  class="card-body">
                        <div class="row mb-1">
                            <div class="col-12">
                                <h4 for="form-code" class="d-flex align-items-center">
                                    Loại<span class="text-danger">(*)</span>
                                    @php($typeDefault = $infoTypeCondition['type'] ?? ("type" . PromotionCondition::TYPE_GIFT_BY_QTY))
                                    <select name="type" class="form-select select-type-condition ms-1" style="width: 200px;">
                                        @foreach(PromotionCondition::TYPE_TEXTS as $key => $type)
                                            <option value="{{ $key }}" @if("type$key" == $typeDefault) selected @endif>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger ms-auto order-type-promotion-repeater"></span>
                                </h4>
                            </div>
                            <div class="d-flex align-items-center p-1">
                                <div class="justify-content-center me-1 text-nowrap">Tên điều kiện<span class="text-danger">(*)</span></div>
                                <div class="w-100">
                                    <input class="form-control" name="nameCondition" value="{{ $infoTypeCondition['name'] ?? '' }}"/>
                                </div>
                            </div>
                                <?php
                                $infoTypeCondition = [];
                                $typeInclude = 'include-products';
                                $includes = [];
                                $typeExclude = 'none';
                                $excludes = [];
                                ?>
                            @foreach (glob(base_path() . '/resources/views/pages/promotion/typeConditions/*.blade.php') as $file)
                                @include('pages.promotion.typeConditions.' . basename(str_replace('.blade.php', '', $file)),
                                    [$typeDefault, $infoTypeCondition, $typeInclude, $includes, $typeExclude, $excludes]
                                )
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <input class="btn btn-outline-success btn-add-new-condition" data-repeater-create type="button" value="Thêm Loại"/>
</div>
