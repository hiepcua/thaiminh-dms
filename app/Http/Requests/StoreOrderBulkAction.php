<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderBulkAction extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Helper::userCan('doi_trang_thai_giao_hang')
            || Helper::userCan('doi_trang_thai_xoa_don');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ids' => [
                'required',
                function ($attribute, $value, $fail) {
                    $ids = explode(',', $value);
                    $ids = array_map('trim', $ids);
                    $ids = array_filter($ids);
                    if (!$ids) {
                        $fail('Không có đơn nào được chọn.');
                    }
                    $this->request->add(['store_ids' => $ids]);
                }
            ]
        ];
    }
}
