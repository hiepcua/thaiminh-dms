<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RevenuePeriodRequest extends FormRequest
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
        return [
            'product_type' => 'required',
            'rank_id'      => 'required',
            'period_from'  => 'required',
            'store_type'   => 'required',
            'region_apply' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'product_type' => 'loại hàng',
            'rank_id'      => 'hạng',
            'period_from'  => 'chu kỳ bắt đầu',
            'period_to'    => 'chu kỳ kết thúc',
            'store_type'   => 'loại cửa hàng',
            'region_apply' => 'khu vực áp dụng',
        ];
    }
}
