<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveNewStoreRequest extends FormRequest
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
            'code' => ['required','unique:stores,code'],
        ];
    }

    public function messages()
    {
        return array_merge(parent::messages(), [
            'code.required' => 'Mã nhà thuốc không được bỏ trống',
            'code.unique' => 'Mã nhà thuốc đã được sử dụng',
        ]);
    }
}
