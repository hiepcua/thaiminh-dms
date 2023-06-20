<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use Illuminate\Foundation\Http\FormRequest;

class SACreateEditAgencyOrderRequest extends FormRequest
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
            'title' => ['required'],
            'booking_at' => ['required'],
            'agency_id' => ['required'],
            'products' => ['required'],
        ];
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'booking_at' => 'ngày nhập hàng',
            'locality_id' => 'địa bàn',
            'agency_id' => 'đại lý',
            'products' => 'sản phẩm',
        ]);
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'title'        => Helper::convertSpecialCharInput($this->title),
        ]);
    }
}
