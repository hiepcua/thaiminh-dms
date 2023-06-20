<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use App\Models\Agency;
use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEditAgencyRequest extends FormRequest
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
        $agencyId = $this->route()->parameter('agency');
        $rules    = [
            'name'             => ['required'],
            'division_id'      => ['required'],
            'locality_ids'     => ['required'],
            'status'           => ['required', 'integer', 'between:0,1'],
            'order_code'       => ['required', Rule::unique('agencies')->ignore($agencyId)],
            'pay_number'       => ['required'],
            'pay_service_cost' => ['required', 'min:0'],
            'code'             => [
                $agencyId ? 'nullable' : 'required',
                'starts_with:DL',
                Rule::unique('agencies')->ignore($agencyId)
            ]
        ];

        return $rules;
    }

    public function attributes()
    {
        return array_merge(Agency::ATTRIBUTES_TEXT, [
            'division_id'      => 'Khu vực',
            'locality_ids'     => 'Địa bàn',
            'order_code'       => 'mã số đơn TT',
            'pay_number'       => 'số tài khoản',
            'pay_service_cost' => 'phí dịch vụ',
        ]);
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name'        => Helper::convertSpecialCharInput($this->name),
            'address'     => Helper::convertSpecialCharInput($this->address),
            'vat_buyer'   => Helper::convertSpecialCharInput($this->vat_buyer),
            'vat_company' => Helper::convertSpecialCharInput($this->vat_company),
            'vat_number'  => Helper::convertSpecialCharInput($this->vat_number),
            'vat_email'   => Helper::convertSpecialCharInput($this->vat_email),
        ]);
    }
}
