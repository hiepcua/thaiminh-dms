<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
        $product_id = $this->route('product') ?: 0;
        return [
            'code'            => ['required', 'unique:products' . ($product_id ? ',id,' . $product_id : '')],
            'name'            => 'required',
            'company_id'      => 'required',
            'wholesale_price' => 'required',
            'price'           => 'required',
            'unit'            => 'required',
            'point'           => ['required', 'min:1', 'integer']
        ];
    }

    public function attributes()
    {
        return [
            'code'            => 'mã sản phẩm',
            'name'            => 'tên sản phẩm',
            'company_id'      => 'công ty',
            'wholesale_price' => 'giá buôn',
            'price'           => 'giá khuyến nghị',
            'point'           => 'điểm',
            'unit'            => 'quy cách đóng gói'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name'         => Helper::convertSpecialCharInput($this->name),
            'display_name' => Helper::convertSpecialCharInput($this->display_name),
            'code'         => Helper::convertSpecialCharInput($this->code),
        ]);
    }
}
