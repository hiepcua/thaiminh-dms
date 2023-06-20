<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use App\Models\Organization;
use App\Services\OrganizationService;
use Illuminate\Foundation\Http\FormRequest;

class CreateEditOrganization extends FormRequest
{
    protected $organizationService;
    protected $routeName;

    public function __construct(
        OrganizationService $organizationService,
        array               $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->organizationService = $organizationService;
    }

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
        $this->routeName     = $this->route()->getName();
        $func_valid_province = function ($attribute, $value, $fail) {
            $type = (int)$this->request->get('type');
            if (!$value && $type == Organization::TYPE_DIA_BAN) {
                $fail('Trường :attribute không được bỏ trống.');
            }
        };

        $rules = [
            'name'        => ['required', 'string'],
            'type'        => ['required', 'integer'],
            'parent_id'   => ['integer', function ($attribute, $value, $fail) {
                $type = (int)$this->request->get('type');
                if (!$value && $type > Organization::TYPE_TONG_CONG_TY) {
                    $fail('Trường :attribute không được bỏ trống.');
                }
            },],
            'province_id' => [$func_valid_province],
            'districts'   => [$func_valid_province],
            'status'      => ['required'],
        ];
        if ($this->routeName == 'admin.organizations.update') {
            // Kiem tra trang thai cua cap CHA
            // Neu trang thai cap CHA la INACTIVE thi cap con khong duoc cap nhat ACTIVE
            $rules['status'][] = function ($attribute, $value, $fail) {
                $parent_id = $this->request->get('parent_id');
                if ($value == Organization::STATUS_ACTIVE && $parent_id) {
                    $parent = $this->organizationService->getParent($parent_id);
                    if ($parent->status == Organization::STATUS_INACTIVE) {
                        $fail('Trạng thái cấp cha (' . $parent->name . ') đang không hoạt động.');
                    }
                }
            };
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'type'      => 'loại',
            'parent_id' => 'cấp cha',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name'        => Helper::convertSpecialCharInput($this->name),
        ]);
    }
}
