<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Repositories\ProductGroupPriority\ProductGroupPriorityRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use App\Http\Requests\ProductGroupPriorityRequest;
use App\Models\ProductGroup;
use App\Helpers\Helper;
use App\Services\ProductGroupPriorityService;

class ProductGroupPriorityController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(ProductGroupPriorityRepositoryInterface $repository, ProductGroupPriorityService $service)
    {
        $this->repository = $repository;
        $this->service    = $service;
        $this->middleware('permission:xem_nhom_va_san_pham_uu_tien')->only('index', 'indexProduct');
        $this->middleware('permission:sua_nhom_va_san_pham_uu_tien')->only('edit', 'update');
        $this->middleware('permission:them_nhom_va_san_pham_uu_tien')->only('create', 'createProduct', 'store');
        $this->middleware('permission:xoa_nhom_va_san_pham_uu_tien')->only('destroy');
    }

    public function index(Request $request)
    {
        $productTypes = ProductGroup::PRODUCT_TYPES;
        $search                = $request->get('search', []);
        $searchProductType = (isset($search['product_type'])) ? $search['product_type'] : 1;

        $formOptions           = $this->service->formOptions();
        $formOptions['action'] = route('admin.product-group-priorities.index');
        $results               = $this->service->getTable($search);

        $periods = Helper::getPeriodByProductType($productTypes);
        $periods = $periods[$searchProductType];

        $newPeriods = [];
        foreach ($periods as $_period){
            $newPeriods[$_period['started_at']] = $_period['name'];//$_period['started_at'];
        }
        $periods = $newPeriods;

        //dd($newPeriods);

        //dd($periods);

        return view('pages.product_group_priorities.index', compact(
                'formOptions',
                'results',
                'productTypes',
                'periods'
            )
        );
    }

    public function indexProduct(Request $request, $product_id)
    {
        $product_id        = $product_id ?? '';
        $search            = $request->get('search', []);
        $search['product'] = $product_id;
        $showOptions       = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => ["column" => "product_group_priorities.created_at", "type" => "DESC"]
        ]);
        $table             = $this->service->getTable2($search, $showOptions);

        return view('pages.product_group_priorities.index-product', compact(
            'product_id',
            'table',
        ));
    }

    public function create(Request $request)
    {
        $productTypes = ProductGroup::PRODUCT_TYPES;
        $priority               = $this->repository->getModel();
        $priority_id            = $priority->id;
        $formOptions            = $this->service->formOptions($priority);
        $formOptions['action']  = route('admin.product-group-priorities.store');
        $default_values         = $formOptions['default_values'];
        $perEdit                = true;
        $productId              = $request->get('product') ?? '';
        $formOptions['backUrl'] = $productId != '' ? route('admin.product-group-priorities.history', $productId) : route('admin.product-group-priorities.index');
        $periods = Helper::getPeriodByProductType($productTypes);

        //Remove start_period time less than today
        foreach ($periods as $productType => $_periodData){
            foreach ($_periodData as $_index => $_data){
                $date = Carbon::parse(date("Y-m-d"))->subMonth(2)->firstOfMonth()->toDateString();
                if(strtotime($_data["started_at"]) <= strtotime($date)){
                    unset($periods[$productType][$_index]);
                }

            }
        }

        $periodsTo = $periods;

        return view('pages.product_group_priorities.add-edit', compact(
            'formOptions',
            'default_values',
            'priority_id',
            'perEdit',
            'productTypes',
            'periods',
            'periodsTo'
        ));
    }

    public function store(ProductGroupPriorityRequest $request)
    {
        $this->service->create($request->all());
        Helper::successMessage('Thiết lập ưu tiên thành công');
        return redirect()->route('admin.product-group-priorities.index');
    }

    public function show($id)
    {
        $item = $this->repository->find($id);
    }

    public function edit($id)
    {
        $priority = $this->repository->find($id);
        if (!$priority) return false;

        $productTypes = ProductGroup::PRODUCT_TYPES;
        $priority_id            = $priority->id;
        $formOptions            = $this->service->formOptions($priority);
        $formOptions['action']  = route('admin.product-group-priorities.update', $priority_id);
        $default_values         = $formOptions['default_values'];
        $currentDate            = Carbon::now()->format('Y-m-d');
        $period_from            = $default_values['period_from'];
        $period_to            = $default_values['period_to'];
        $perEdit                = !($currentDate > $period_from);
        $formOptions['backUrl'] = route('admin.product-group-priorities.index');
        $periods = Helper::getPeriodByProductType($productTypes);
        $periodsTo = $periods;
        foreach ($periods as $productType => $_periodData){
            foreach ($_periodData as $_index => $_data){
                //$date = Carbon::parse($period_from)->subMonth(2)->firstOfMonth()->toDateString();
                if($period_from != $_data["started_at"] && (strtotime($_data["started_at"]) <= strtotime(date('Y-m-d')))){
                    unset($periods[$productType][$_index]);
                }

            }
        }

        foreach ($periodsTo as $productType => $_periodData){
            foreach ($_periodData as $_index => $_data){
                $date = Carbon::parse(date('Y-m-d'))->subMonth()->firstOfMonth()->toDateString();
                if($period_to != $_data["ended_at"] && (strtotime($_data["started_at"]) < strtotime($date))){
                    unset($periodsTo[$productType][$_index]);
                }

            }
        }

        return view('pages.product_group_priorities.add-edit', compact(
            'formOptions',
            'default_values',
            'priority_id',
            'perEdit',
            'productTypes',
            'periods',
            'periodsTo'
        ));
    }

    public function update(ProductGroupPriorityRequest $request, $id)
    {
        $this->service->update($id, $request->all());
        Helper::successMessage('Cập nhật ưu tiên thành công');
        return redirect()->route('admin.product-group-priorities.index');
    }

    public function destroy($id)
    {
        try {
            $this->repository->delete($id);

            return response()->json([
                'message' => 'Nhóm và sản phẩm ưu tiên đã được xóa thành công!'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }
}
