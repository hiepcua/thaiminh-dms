<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use App\Models\Gift;
use Illuminate\Foundation\Http\FormRequest;

class CreateEditGiftRequest extends FormRequest
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
        $rules = [
            'code'  => ['required'],
            'name'  => ['required'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999999999999'],
        ];

        if (request()->route()->getName() == 'admin.gift.store') {
            $rules['code'] = array_merge($rules['code'], ['unique:gifts,code']);
        }

        return $rules;
    }

    public function attributes()
    {
        return Gift::ATTRIBUTES_TEXT;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name' => Helper::convertSpecialCharInput($this->name),
            'code' => Helper::convertSpecialCharInput($this->code),
        ]);
    }
}
