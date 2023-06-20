<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use App\Services\OrganizationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DeleteOrganization extends FormRequest
{
    public function __construct(
        protected OrganizationService $organizationService,
        array                         $query = [],
        array                         $request = [],
        array                         $attributes = [],
        array                         $cookies = [],
        array                         $files = [],
        array                         $server = [],
                                      $content = null
    )
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Helper::userCan('xoa_cay_so_do');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    public function withValidator(Validator $validator)
    {
        $organization_id = $this->route('organization');
        $check_delete    = $this->organizationService->checkDelete($organization_id);
        if (!$check_delete['deleted']) {
            $validator->after(function ($validator) use ($check_delete) {
                $validator->errors()->add('id', $check_delete['message']);
            });
        }
    }
}
