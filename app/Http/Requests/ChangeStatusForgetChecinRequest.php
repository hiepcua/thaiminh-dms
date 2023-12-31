<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusForgetChecinRequest extends FormRequest
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
            'id'           => 'required',
            'reviewerNote' => 'required',
            'status'       => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'reviewerNote' => 'Ghi chú',
            'status'       => 'Trạng thái',
        ];
    }
}
