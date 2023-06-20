<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use Illuminate\Foundation\Http\FormRequest;

class CreateEditLineRequest extends FormRequest
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
            'locality'    => ['required'],
            'name'        => ['required'],
            'day_of_week' => [],
            'stores'      => [],
        ];
    }

    public function attributes()
    {
        return [
            'locality'    => 'địa bàn',
            'name'        => 'tên tuyến',
            'day_of_week' => 'thứ trong tuần',
            'stores'      => 'nhà thuốc ',
            'stores.*'    => 'nhà thuốc ',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name' => Helper::convertSpecialCharInput($this->name)
        ]);
    }
}
