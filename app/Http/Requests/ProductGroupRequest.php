<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use App\Repositories\ProductGroupPriority\ProductGroupPriorityRepositoryInterface;
use App\Repositories\ProductGroup\ProductGroupRepositoryInterface;

class ProductGroupRequest extends FormRequest
{
    protected ProductGroupPriorityRepositoryInterface $productGroupPriorityRepository;
    protected ProductGroupRepositoryInterface $repository;

    public function __construct(
        ProductGroupPriorityRepositoryInterface $productGroupPriorityRepository,
        ProductGroupRepositoryInterface         $repository,
    )
    {
        parent::__construct();
        $this->productGroupPriorityRepository = $productGroupPriorityRepository;
        $this->repository                     = $repository;
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
            'name'   => 'required',
            'status' => 'in:0,1',
        ];
    }

    public function attributes()
    {
        return [
            'name'   => 'tên nhóm sản phẩm',
            'status' => 'trạng thái',
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator)
    {
        $flag = true;
        if (request()->route()->getName() == 'admin.product-groups.update') {
            $productGroupId = $this->route('product_group');
            $productGroup   = $this->repository->find($productGroupId);
            $status         = $this->request->get('status') ?? 0;
            if (!$status) {
                $subGroupID = [];
                $curDate    = Carbon::now()->format('Y-m-d');
                if ($productGroup->parent_id) {
                    // Sub group
                    $subGroupID[] = $productGroup->id;
                } else {
                    // Group
                    $subGroupID = $productGroup->children()->get('id')->pluck('id')->toArray();
                }

                $productGroupPriorities = $this->productGroupPriorityRepository->getListByGroup($subGroupID, $curDate);
                if ($productGroupPriorities->isNotEmpty()) $flag = false;
            }
        }

        $validator->after(function ($validator) use ($flag) {
            if (!$flag) {
                $validator->errors()->add('messages', 'Nhóm sản phẩm hiện tại đang nằm trong một hoặc nhiều chu kỳ ưu tiên đang hoạt động.');
            }
        });
    }
}
