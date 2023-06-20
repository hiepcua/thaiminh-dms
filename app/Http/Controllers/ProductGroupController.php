<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Http\Requests\ProductGroupRequest;
use App\Models\ProductGroup;
use App\Repositories\ProductGroup\ProductGroupRepositoryInterface;
use App\Services\ProductGroupService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductGroupController extends Controller
{
    protected $repository, $service;

    public function __construct(ProductGroupRepositoryInterface $repository, ProductGroupService $service)
    {
        $this->repository = $repository;
        $this->service    = $service;
    }

    public function index(Request $request)
    {
        $page_title  = '10.2.3 Nhóm SP';
        $search      = $request->get('search', []);
        $showOptions = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => ["column" => "product_groups.created_at", "type" => "DESC"]
        ]);
        $table       = $this->service->getTable($search, $showOptions);

        return view('pages.product_groups.index', compact('page_title', 'table'));
    }

    public function create()
    {
        $productTypes          = ProductGroup::PRODUCT_TYPES;
        $product_group         = $this->repository->getModel();
        $product_group_id      = $product_group->id;
        $formOptions           = $this->service->formOptions();
        $formOptions['action'] = route('admin.product-groups.store');
        $default_values        = $formOptions['default_values'];

        return view('pages.product_groups.add-edit', compact('product_group_id', 'formOptions', 'default_values','productTypes'));
    }

    public function store(Request $request)
    {
        $attributes = $request->all();
        $this->repository->create($attributes);

        return redirect(route('admin.product-groups.index'));
    }

    public function show($id)
    {
        $item = $this->repository->find($id);
    }

    public function edit($product_group_id)
    {
        $productTypes          = ProductGroup::PRODUCT_TYPES;
        $product_group         = $this->repository->find($product_group_id);
        $formOptions           = $this->service->formOptions($product_group);
        $formOptions['action'] = route('admin.product-groups.update', $product_group_id);
        $default_values        = $formOptions['default_values'];
        return view('pages.product_groups.add-edit', compact('product_group_id', 'formOptions', 'default_values','productTypes'));
    }

    public function update(ProductGroupRequest $request, $id)
    {
        $this->service->update($id, $request->all());
        return redirect()->route('admin.product-groups.index')
            ->with('successMessage', 'Cập nhóm sản phẩm thành công');
    }

    public function destroy($id)
    {
        try {
            $result = $this->service->delete($id);

            return response()->json([
                'message' => $result['message'],
                'icon'    => $result['icon'],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }
}
