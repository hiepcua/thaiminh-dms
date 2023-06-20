<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveAgencyInventoryRequest extends FormRequest
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
            'agency_id'  => 'required',
            'product_id' => 'required',
            'month'      => 'required',
            'year'       => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'agency_id'  => 'đại lý',
            'product_id' => 'sản phẩm',
            'month'      => 'tháng',
            'year'       => 'năm',
        ];
    }
}
