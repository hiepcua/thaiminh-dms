<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use App\Models\ProductGroupPriority;
use App\Services\ProductGroupPriorityService;
use App\Repositories\ProductGroupPriority\ProductGroupPriorityRepositoryInterface;

class ProductGroupPriorityRequest extends FormRequest
{
    protected ProductGroupPriorityService $service;
    protected ProductGroupPriorityRepositoryInterface $repository;

    public function __construct(ProductGroupPriorityService $service, ProductGroupPriorityRepositoryInterface $repository)
    {
        $this->service    = $service;
        $this->repository = $repository;
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
        return [
            'product_id'   => 'required',
            'sub_group_id' => 'required',
            'store_type'   => 'required',
            'region_apply' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'product_id'   => 'sản phẩm',
            'sub_group_id' => 'nhóm sản phẩm',
            'store_type'   => 'loại nhà thuốc',
            'region_apply' => 'miền áp dụng',
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator)
    {
        $flag         = true;
        $allow        = true;
        $product_id   = $this->request->get('product_id') ?? '';
        $productType  = $this->request->get('product_type') ?? '';
        $period_from  = $this->request->get('period_from') ?? '';
        $period_to    = $this->request->get('period_to') ?? null;
        $priority_id  = $this->request->get('priority_id') ?? '';
        $store_type   = $this->request->get('store_type') ?? null;
        $region_apply = $this->request->get('region_apply') ?? null;
        $cur_period   = Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d');

        if ($period_to && strtotime($period_from) > strtotime($period_to)) {
            $validator->errors()->add('messages', 'Chu kì đến phải lớn hơn hoặc bằng chu kì từ và phải lớn hơn hoặc bằng thời gian hiện tại');
        }

//        $maxYear    = Carbon::now()->addYears(2)->format('Y');
//        $objPeriods = Helper::periodOptionMultipleYears($minYear, $maxYear);

        // Trong 1 khoảng thời gian một sản phẩm chỉ có 1 ưu tiên
        $checkConflict = $this->service->checkConflictPeriod(
            $productType,
            $product_id,
            $period_from,
            $period_to,
            $priority_id,
            $store_type,
            $region_apply
        );

        //dd($checkConflict);
        $validator->after(function ($validator) use ($checkConflict) {

            if ($checkConflict) {
                $validator->errors()->add('messages', 'Sản phẩm với thiết lập chu kỳ hiện tại đã được thiết lập ưu tiên ở một bản ghi khác');
            }
        });
    }
}
