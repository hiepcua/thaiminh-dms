<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use App\Models\Gift;
use App\Models\Promotion;
use App\Models\PromotionCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEditPromotionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $routeName = request()->route()->getName();

        $rules = [
            'ended_at'                       => ['after_or_equal:started_at', 'date'],
            'status'                         => ['required', Rule::in(Promotion::STATUS)],
            'division_id'                    => ['required'],
            'typesPromotion'                 => ['required'],
            'typesPromotion.*.nameCondition' => ['required'],
            'typesPromotion.*.type'          => ['required'],
        ];

        if ($routeName == 'admin.promotion.update') {
            $rules['name'] = ['required', 'unique:promotions,name,' . request('promotion')];
        }
        if ($routeName == 'admin.promotion.store') {
            $rules['name']       = ['required', 'unique:promotions,name'];
            $rules['started_at'] = ['required', 'date', 'after_or_equal:' . now()->format('Y-m-d')];
        }

        $typePromotions = $this->request->get('typesPromotion', []);
        foreach ($typePromotions as $keyTypePromotion => $typePromotion) {
            if (in_array($typePromotion['type'] ?? '', PromotionCondition::TYPES)) {
                $prefixRule = "typesPromotion.$keyTypePromotion.type" . $typePromotion['type'];

                $rules = array_merge($rules, [
                    "$prefixRule"                   => ['bail', 'required'],
                    "$prefixRule.*.includeProducts" => ['bail', "required_if:$prefixRule.*.includeType,include-products"],
                    "$prefixRule.*.includeGroups"   => ['bail', "required_if:$prefixRule.*.includeType,include-groups"],
                    "$prefixRule.*.excludeProducts" => ['bail', "required_if:$prefixRule.*.excludeType,exclude-products"],
                    "$prefixRule.*.excludeGroups"   => ['bail', "required_if:$prefixRule.*.excludeType,exclude-groups"],
                    "$prefixRule.*.gifts"           => ['bail', 'required'],
                ]);

                switch ($typePromotion['type']) {
                    case PromotionCondition::TYPE_GIFT_BY_QTY:
                        $rules = array_merge($rules, [
                            "$prefixRule.*.gifts.*.productQty" => ['bail', 'required', 'min:1', 'integer',],
                            "$prefixRule.*.gifts.*.gift"       => ['bail', 'required',],
                            "$prefixRule.*.gifts.*.giftQty"    => ['bail', 'required', 'min:1', 'integer',],
                        ]);

                        break;
                    case PromotionCondition::TYPE_GIFT_BY_TOTAL_QTY:
                        $rules = array_merge($rules, [
                            "$prefixRule.*.gifts"           => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.minQty"  => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.minType" => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.gift"    => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.giftQty" => ['bail', 'required'],
                        ]);

                        break;
                    case PromotionCondition::TYPE_GIFT_BY_TOTAL_COST:
                        $rules = array_merge($rules, [
                            "$prefixRule.*.gifts"           => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.minCost" => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.minType" => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.gift"    => ['bail', 'required',],
                            "$prefixRule.*.gifts.*.giftQty" => ['bail', 'required', 'min:1', 'integer',],
                        ]);

                        break;
                    case PromotionCondition::TYPE_GIFT_BY_TOTAL_POINT:
                        $rules = array_merge($rules, [
                            "$prefixRule.*.gifts"            => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.minPoint" => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.minType"  => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.gift"     => ['bail', 'required',],
                            "$prefixRule.*.gifts.*.giftQty"  => ['bail', 'required', 'min:1', 'integer',],
                        ]);

                        break;
                    case PromotionCondition::TYPE_DISCOUNT_BY_QTY:
                        $rules = array_merge($rules, [
                            "$prefixRule.*.gifts"              => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.productQty" => ['bail', 'required', 'min:1', 'integer',],
                            "$prefixRule.*.gifts.*.discount"   => ['bail', 'required', 'min:1',],
                            "$prefixRule.*.gifts.*.type"       => ['bail', 'required', 'min:1', 'integer',],
                        ]);

                        break;
                    case PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_QTY:
                        $rules = array_merge($rules, [
                            "$prefixRule.*.gifts"                 => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.minQty"        => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.minType"       => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.discount"      => ['bail', 'required', 'min:1', 'integer',],
                            "$prefixRule.*.gifts.*.type"          => ['bail', 'required', 'integer',],
                        ]);

                        break;
                    case PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_COST:
                        $rules = array_merge($rules, [
                            "$prefixRule.*.gifts"                 => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.minCost"       => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.minType"       => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.discount"      => ['bail', 'required', 'min:1', 'integer',],
                            "$prefixRule.*.gifts.*.type"          => ['bail', 'required', 'integer',],
                        ]);
                        break;
                    case PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT:
                        $rules = array_merge($rules, [
                            "$prefixRule.*.gifts"                 => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.minPoint"      => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.minType"       => ['bail', 'required'],
                            "$prefixRule.*.gifts.*.discount"      => ['bail', 'required', 'min:1', 'integer',],
                            "$prefixRule.*.gifts.*.type"          => ['bail', 'required', 'integer',],
                        ]);
                        break;
                }
            }
        }

        return $rules;
    }

    public function attributes()
    {

        $newAttributes = array_merge(Promotion::ATTRIBUTES_TEXT, [
            'division_id'    => 'khu vực',
            'typesPromotion' => 'loại khuyến mãi',
        ]);

        $typePromotions = $this->request->get('typesPromotion', []);
        foreach ($typePromotions as $key => $typePromotion) {
            $orderType = $key + 1;
            foreach (PromotionCondition::TYPES as $type) {
                $prefixAttribute = "typesPromotion.$key.type$type";
                $newAttributes   = array_merge($newAttributes, [
                    "typesPromotion.$key.nameCondition"        => "tên điều kiện (Loại: #$orderType)",
                    "typesPromotion.$key.type"                 => "loại điều kiện  (Loại: #$orderType)",
                    "$prefixAttribute"                         => "điều kiện  (Loại: #$orderType)",
                    "$prefixAttribute.*.includeType"           => "loại sản phẩm áp dụng  (Loại: #$orderType)",
                    "$prefixAttribute.*.includeProducts"       => "sản phẩm áp dụng  (Loại: #$orderType)",
                    "$prefixAttribute.*.includeGroups"         => "group được áp dụng  (Loại: #$orderType)",
                    "$prefixAttribute.*.excludeType"           => "loại sản phẩm loại trừ  (Loại: #$orderType)",
                    "$prefixAttribute.*.excludeProducts"       => "sản phẩm loại trừ  (Loại: #$orderType)",
                    "$prefixAttribute.*.excludeGroups"         => "group loại trừ  (Loại: #$orderType)",
                    "$prefixAttribute.*.gifts"                 => "khuyến mãi  (Loại: #$orderType)",
                    "$prefixAttribute.*.gifts.*.gift"          => "quà tặng  (Loại: #$orderType)",
                    "$prefixAttribute.*.gifts.*.giftQty"       => "số lượng quà tặng  (Loại: #$orderType)",
                    "$prefixAttribute.*.gifts.*.productQty"    => "số sản phẩm  (Loại: #$orderType)",
                    "$prefixAttribute.*.gifts.*.minQty"        => "số lượng tối thiểu  (Loại: #$orderType)",
                    "$prefixAttribute.*.gifts.*.minType"       => "số loại tối thiểu  (Loại: #$orderType)",
                    "$prefixAttribute.*.gifts.*.discount"      => "số lượng giảm  (Loại: #$orderType)",
                    "$prefixAttribute.*.gifts.*.type"          => "kiểu giảm giá  (Loại: #$orderType)",
                    "$prefixAttribute.*.gifts.*.minCost"       => "số tiền tối thiểu  (Loại: #$orderType)",
                    "$prefixAttribute.*.gifts.*.limitDiscount" => "số lượng giảm tối đa  (Loại: #$orderType)",
                ]);
            }
        }

        return $newAttributes;
    }

    protected function prepareForValidation()
    {
        $typesPromotion = $this->request->get('typesPromotion');
        foreach ($typesPromotion as $key => $typePromotion) {
            $typesPromotion[$key]['nameCondition'] = Helper::convertSpecialCharInput($typePromotion['nameCondition']);
        }
        $this->merge([
            'name' => Helper::convertSpecialCharInput($this->name),
            'typesPromotion' => $typesPromotion,
        ]);
    }
}
