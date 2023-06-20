<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PosterRequest extends FormRequest
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
            'name'                => 'required',
            'product_id'          => 'required',
            'range_date'          => 'required',
            'reward_month'        => 'required',
            'reward_amount'       => 'required',
            'acceptance_date.*.*' => 'required',
            'acceptance_date'     => 'required',
            'division_id'         => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'name'                => 'tên CT',
            'product_id'          => 'sản phẩm',
            'range_date'          => 'ngày dán Poster',
            'reward_month'        => 'số lượng trả thưởng',
            'reward_amount'       => 'số lượng trả thưởng',
            'acceptance_date.*.*' => 'ngày nghiệm thu',
            'acceptance_date'     => 'ngày nghiệm thu',
            'division_id'         => 'khu vực',
        ];
    }
}
