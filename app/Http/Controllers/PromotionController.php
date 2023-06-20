<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Http\Requests\CreateEditPromotionRequest;
use App\Repositories\Promotion\PromotionRepositoryInterface;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PromotionController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(
        PromotionRepositoryInterface $repository,
        PromotionService $service
    ) {
        $this->repository = $repository;
        $this->service    = $service;

        $this->middleware('permission:xem_danh_sach_chuong_trinh_khuyen_mai')->only('index');
        $this->middleware('permission:them_chuong_trinh_khuyen_mai')->only('create', 'store');
        $this->middleware('permission:sua_chuong_trinh_khuyen_mai')->only('show', 'update');
        $this->middleware('permission:xoa_chuong_trinh_khuyen_mai')->only('destroy');
    }

    public function index(Request $request)
    {
        $page_title = '10.3.2 CT KM';
        $search     = $request->get('search', []);
        $formOptions    = $this->service->formOptions();
        $defaultValues  = $formOptions['default_values'] ?? [];
        $promotions = $this->service->getDataForScreenList($search);

        return view('pages.promotion.index', compact('promotions', 'page_title', 'formOptions', 'defaultValues'));
    }

    public function create()
    {
        $page_title            = '10.4.2 Thêm chương trình khuyến mãi';
        $formOptions           = $this->service->formOptions();
        $formOptions['action'] = route('admin.promotion.store');
        $default_values        = $formOptions['default_values'] ?? [];

        $view_data = compact('formOptions', 'default_values', 'page_title');

        return view('pages.promotion.create-or-edit', $view_data);
    }

    public function store(CreateEditPromotionRequest $request)
    {
        $attributes = $request->all();

        $this->service->create($attributes);

        return redirect()->route('admin.promotion.index')
            ->with('successMessage', 'Chương trình khuyến mãi đã được thêm mới thành công');
    }

    public function show($id)
    {
        $page_title            = '10.3.2 Sửa chương trình khuyến mãi';
        $formOptions           = $this->service->formOptions($this->repository->findOrFail($id));
        $formOptions['action'] = route('admin.promotion.update', $id);
        $default_values        = $formOptions['default_values'] ?? [];

        $view_data = compact('formOptions', 'default_values', 'page_title');

        return view('pages.promotion.create-or-edit', $view_data);
    }

    public function update(CreateEditPromotionRequest $request, $id)
    {
        $attributes = $request->all();
        $this->service->update($id, $attributes);

        return redirect()->route('admin.promotion.index')
            ->with('successMessage', 'Chương trình khuyến mãi đã được cập nhập thành công');
    }

    public function destroy($id)
    {
        try {
            $this->repository->delete($id);

            return response()->json([
                'message' => 'Chương trình khuyến mãi đã được xóa thành công!'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }
}
