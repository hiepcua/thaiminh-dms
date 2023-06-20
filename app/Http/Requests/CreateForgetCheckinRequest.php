<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateForgetCheckinRequest extends FormRequest
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
            "tdv_id"        => "required",
            "checkin_at"    => "required",
            "store_id"      => "required",
            "checkout_at"   => "required",
            "reviewer_note" => "required",
        ];
    }
}
