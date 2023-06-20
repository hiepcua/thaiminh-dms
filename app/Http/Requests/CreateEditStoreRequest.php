<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEditStoreRequest extends FormRequest
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
            'type'            => ['required', Rule::in(array_keys(Store::STORE_TYPE))],
            'name'            => ['required', 'max:255'],
            'code'            => ['max:20'],
            'organization_id' => ['required', 'integer'],
            'province_id'     => ['required', 'integer'],
            'district_id'     => ['required', 'integer'],
            'ward_id'         => ['required', 'integer'],
            'address'         => ['required', 'max:255'],
            'phone_owner'     => ['required', 'max:50'],
            'phone_web'       => ['max:50'],
            'lat'             => [],
            'lng'             => [],
            'parent_id'       => [],
            'vat_parent'      => [],
            'vat_buyer'       => ['max:255'],
            'vat_company'     => ['max:255'],
            'vat_address'     => ['max:255'],
            'vat_number'      => ['max:20'],
            'vat_email'       => ['max:100'],
            'status'          => [],
            'line'            => [],
            'line_period'     => [],
            'number_visit'    => [],
        ];

        $code        = $this->request->get('code') ?? '';
        $hasParent   = $this->request->get('has_parent') ?? '';
        $lat         = $this->request->get('lat') ?? '';
        $lng         = $this->request->get('lng') ?? '';
        $numberVisit = $this->request->get('number_visit') ?? '';
        $linePeriod  = $this->request->get('line_period') ?? '';
        $currenRoute = request()->route()->getName();

        if ($currenRoute == 'admin.stores.store' || $currenRoute == 'admin.new-stores.update' && $code !== '') {
            $rules['code'] = array_merge($rules['code'], ['unique:stores,code']);
        }
        if ($currenRoute == 'admin.stores.store' || $currenRoute == 'admin.stores.update') {
            $rules['status'] = array_merge($rules['status'], ['required', Rule::in(array_keys(Store::STATUS_TEXTS))]);
        }
        if ($hasParent == 1) {
            $rules['parent_id'] = array_merge($rules['parent_id'], ['required']);
        }
        if ($lat != '') {
            $rules['lat'] = array_merge($rules['lat'], ['regex:/^\d+(\.\d+)?$/']);
        }
        if ($lng != '') {
            $rules['lng'] = array_merge($rules['lng'], ['regex:/^\d+(\.\d+)?$/']);
        }
        if ($numberVisit != '') {
            $rules['number_visit'] = array_merge($rules['number_visit'], ['required', Rule::in(array_keys(Store::LINE_PERIOD))]);
        }
        if (
            $currenRoute == 'admin.stores.store' ||
            $currenRoute == 'admin.stores.update' ||
            $currenRoute == 'admin.tdv.store.store' ||
            $currenRoute == "admin.tdv.store.update" ||
            $currenRoute == "admin.tdv.new-stores.update"
        ) {
            $rules['line'] = array_merge($rules['line'], ['required']);
        }
        if ($linePeriod != '') {
            $rules['line_period'] = array_merge($rules['line_period'], ['required', Rule::in(array_keys(Store::LINE_PERIOD))]);
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'type'            => 'loại nhà thuốc',
            'name'            => 'tên nhà thuốc',
            'code'            => 'mã nhà thuốc',
            'organization_id' => 'địa bàn',
            'province_id'     => 'TP/ Tỉnh',
            'district_id'     => 'Quận/ Huyện',
            'ward_id'         => 'Phường/ Xã',
            'address'         => 'địa chỉ',
            'phone_owner'     => 'SĐT nhận TT',
            'phone_web'       => ['max:50'],
            'lat'             => 'kinh độ',
            'lng'             => 'vĩ độ',
            'parent_id'       => 'nhà thuốc cha',
            'vat_parent'      => 'viết HĐ về thông tin nhà thuốc cha',
            'vat_buyer'       => 'người mua hàng',
            'vat_company'     => 'tên công ty',
            'vat_address'     => 'địa chỉ',
            'vat_number'      => 'mã số thuế',
            'vat_email'       => 'email',
            'status'          => 'trạng thái',
            'line'            => 'tuyến',
            'line_period'     => 'số lần thăm/ tháng',
            'number_visit'    => 'số lần thăm/ tháng',
        ];
    }

    public function messages()
    {
        return array_merge(parent::messages(), [
            'code.unique' => 'Mã nhà thuốc đã được sử dụng'
        ]);
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name'        => Helper::convertSpecialCharInput($this->name),
            'code'        => Helper::convertSpecialCharInput($this->code),
            'address'     => Helper::convertSpecialCharInput($this->address),
            'phone_owner' => Helper::convertSpecialCharInput($this->phone_owner),
            'phone_web'   => Helper::convertSpecialCharInput($this->phone_web),
            'vat_buyer'   => Helper::convertSpecialCharInput($this->vat_buyer),
            'vat_number'  => Helper::convertSpecialCharInput($this->vat_number),
            'vat_email'   => Helper::convertSpecialCharInput($this->vat_email),
        ]);
    }
}
