<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Http\Requests\ProductRequest;
use App\Services\AgencyOrderService;
use Illuminate\Http\Response;
use App\Services\ProductService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Helpers\Helper;

class ProductController extends Controller
{
    protected $repository, $service;
    private $companies;
    protected $agencyOrderService;

    public function __construct(ProductRepositoryInterface $repository,
        AgencyOrderService $agencyOrderService,
        ProductService $service
    ){
        $this->repository = $repository;
        $this->companies = Product::COMPANIES;
        $this->service    = $service;
        $this->agencyOrderService = $agencyOrderService;
    }

    public function index(Request $request)
    {
        $search      = $request->get('search', []);
        $companies   = $this->companies;
        $showOptions = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "products.created_at", "type" => "DESC"]]
        ]);
        $table       = $this->service->getTable($search, $showOptions);

        return view('pages.products.index', compact(
            'companies',
            'table',
        ));
    }

    public function create()
    {
        $product               = $this->repository->getModel();
        $product_id            = $product->id;
        $formOptions           = $this->service->formOptions();
        $formOptions['action'] = route('admin.products.store');
        $default_values        = $formOptions['default_values'];
        $all_users             = $formOptions['all_users'];
        $companies             = $this->companies;
        $product_user          = [];

        return view('pages.products.add-edit', compact(
            'product_id',
            'formOptions',
            'default_values',
            'companies',
            'all_users',
            'product_user'
        ));
    }

    public function store(ProductRequest $request)
    {
        $attributes = $request->all();
        $this->repository->create($attributes);
        Helper::successMessage('Thêm mới sản phẩm thành công');
        return redirect()->route('admin.products.index');
    }

    public function show($id)
    {
        $item = $this->repository->find($id);
    }

    public function edit($id)
    {
        $product               = $this->repository->findOrFail($id);
        $product_id            = $product->id;
        $formOptions           = $this->repository->formOptions($product);
        $formOptions['action'] = route('admin.products.update', $product_id);
        $default_values        = $formOptions['default_values'];
        $all_users             = $formOptions['all_users'];
        $companies             = $this->companies;
        $product_user          = $product->bm_users()->get();

        return view('pages.products.add-edit', compact(
            'product_id',
            'formOptions',
            'default_values',
            'companies',
            'all_users',
            'product_user'
        ));
    }

    public function update(ProductRequest $request, $id)
    {
        $attributes               = $request->all();
        $attributes['updated_by'] = Auth::id();
        $this->repository->update($id, $attributes);
        return redirect(route('admin.products.index'));
    }

    public function destroy($id)
    {
        $this->repository->delete($id);
    }

    public function getProductGrouped(Request $request)
    {
        try {
            $bookingAt = $request->get('bookingAt', []);
            $products = $this->agencyOrderService->getProductHasGrouped([
                'from' => $bookingAt['from'] ?? now()->format('Y-m-d'),
                'to' => $bookingAt['to'] ?? now()->format('Y-m-d'),
            ]);

            return response()->json([
                'products' => $products
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function productByPriority(Request $request)
    {
        $search      = $request->get('search', []);
        $results     = $this->repository->paginate(20, [], $search);
        $formOptions = $this->service->formOptions();
        $companies   = $this->companies;
        return view('pages.products.product_priority', compact(
            'results',
            'companies',
            'formOptions'
        ));
    }
}
