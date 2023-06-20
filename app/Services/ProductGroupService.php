<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Helpers\TableHelper;
use App\Models\ProductGroup;
use App\Repositories\ProductGroup\ProductGroupRepositoryInterface;
use App\Repositories\ProductGroupPriority\ProductGroupPriorityRepositoryInterface;
use Carbon\Carbon;

class ProductGroupService extends BaseService
{
    protected $repository;
    protected $productGroupPriorityRepository;

    public function __construct(
        ProductGroupRepositoryInterface         $repository,
        ProductGroupPriorityRepositoryInterface $productGroupPriorityRepository,
    )
    {
        parent::__construct();
        $this->repository                     = $repository;
        $this->productGroupPriorityRepository = $productGroupPriorityRepository;
    }

    public function setModel()
    {
        return new ProductGroup();
    }

    public function getTable($requestParams = [], $showOption = [])
    {
        $showOption = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [
                [
                    "column" => "product_groups.created_at",
                    "type"   => "DESC"
                ]
            ]
        ], $showOption);

        $productGroups = $this->repository->getByRequest(
            with: ['parent'],
            requestParams: $requestParams,
            showOption: $showOption
        );

        $currentUser           = Helper::currentUser();
        $canEditProductGroup   = $currentUser->can('sua_nhom_san_pham');
        $canDeleteProductGroup = $currentUser->can('xoa_nhom_san_pham');
        $cur_page              = $productGroups->currentPage();
        $per_page              = $productGroups->perPage();

        $productTypes          = ProductGroup::PRODUCT_TYPES;

        $productGroups->map(function ($productGroup, $key) use ($productTypes,$canEditProductGroup, $canDeleteProductGroup, $cur_page, $per_page) {
            $productGroup->stt    = ($key + 1) + ($cur_page - 1) * $per_page;

            $productGroup->product_type_name = $productTypes[@$productGroup->product_type]['text'] ?? '';

            $productGroup->status = match ($productGroup->status) {
                ProductGroup::STATUS_ACTIVE => '<span class="badge badge-light-success rounded-3" style="padding: 5px 10px">' . ProductGroup::STATUS_TEXTS[ProductGroup::STATUS_ACTIVE] . '</span>',
                ProductGroup::STATUS_INACTIVE => '<span class="badge badge-light-secondary rounded-3" style="padding: 5px 10px">' . ProductGroup::STATUS_TEXTS[ProductGroup::STATUS_INACTIVE] . '</span>',
            };
            $productGroup->name   = '<span class="' . ($productGroup->parent_id ? 'ps-1' : '') . '">' . $productGroup->name . '</span>';
            $productGroup->parent = $productGroup->parent->name ?? '';
            if ($canEditProductGroup) {
                $productGroup->features .= '<a class="btn btn-sm btn-icon"
                   href="' . route('admin.product-groups.edit', $productGroup->id) . '">
                    <i data-feather="edit" class="font-medium-2 text-body"></i>
                </a>';
            }

            if ($canDeleteProductGroup) {
                $productGroup->features .= '<button class="btn-delete-product-group btn btn-sm btn-icon delete-record waves-effect waves-float waves-light"
                    type="button"
                    data-action="' . route('admin.product-groups.destroy', $productGroup->id) . '">
                    <i data-feather="trash" class="font-medium-2 text-body"></i>
                </button>';
            }

            return $productGroup;
        });

        return new TableHelper(
            collections: $productGroups,
            nameTable: 'product-group-list',
        );
    }

    public function formOptions($model = null): array
    {
        $options            = parent::formOptions($model);
        $options['parents'] = ProductGroup::query()->where('parent_id', 0)->get();
        return $options;
    }

    public function update(int $id, array $attributes = [])
    {
        $productGroup = $this->repository->find($id);
        if (!$productGroup) return abort(404);
        $arr           = [];
        $arr['name']   = $attributes['name'] ?? '';
        $arr['note']   = $attributes['note'] ?? '';
        $arr['status'] = $attributes['status'] ?? 0;
        $arr['product_type']   = (int)$attributes['product_type'] ?? '';
        return $this->repository->update($id, $arr);
    }

    public function delete($id)
    {
        $result       = ['message' => 'Không tìm thấy nhóm sản phẩm', 'icon' => 'error'];
        $productGroup = $this->repository->find($id);
        if ($productGroup->parent_id) {
            // Sub group
            $curDate                = Carbon::now()->format('Y-m-d');
            $productGroupPriorities = $this->productGroupPriorityRepository->getListByGroup([$productGroup->id], $curDate);
            if($productGroupPriorities->isEmpty()){
                $this->repository->delete($productGroup->id);
                $result['message'] = 'Xóa nhóm sản phẩm thành công';
                $result['icon']    = 'success';
            }else{
                $result['message'] = 'Nhóm sản phẩm hiện tại đang nằm trong một hoặc nhiều chu kỳ ưu tiên đang hoạt động.';
                $result['icon']    = 'error';
            }
        } else {
            // Group
            $children = $productGroup->children()->get('id')->pluck('id')->toArray();
            if (empty($children)) {
                $this->repository->delete($productGroup->id);
                $result['message'] = 'Xóa nhóm sản phẩm thành công';
                $result['icon']    = 'success';
            } else {
                $result['message'] = 'Phải các nhóm sản phẩm con trước khi xóa nhóm sản phẩm cha';
                $result['icon']    = 'error';
            }
        }
        return $result;
    }
}
