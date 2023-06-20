<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Http\Requests\CreateEditGiftRequest;
use App\Repositories\Gift\GiftRepositoryInterface;
use App\Services\GiftService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GiftController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(
        GiftRepositoryInterface $repository,
        GiftService $service
    ) {
        $this->repository = $repository;
        $this->service    = $service;
    }

    public function index(Request $request)
    {
        $page_title = '10.3.1 DS quà tặng';
        $search     = $request->get('search', []);
        $formOptions    = $this->service->formOptions();
        $defaultValues  = $formOptions['default_values'] ?? [];
        $gifts = $this->service->getDataForScreenList($search);

        return view('pages.gift.index', compact('gifts', 'page_title', 'formOptions', 'defaultValues'));
    }

    public function create()
    {
        $page_title            = '10.4.1 Thêm quà tặng';
        $formOptions           = $this->service->formOptions();
        $formOptions['action'] = route('admin.gift.store');
        $default_values        = $formOptions['default_values'] ?? [];

        $view_data = compact('formOptions', 'default_values', 'page_title');

        return view('pages.gift.create-or-edit', $view_data);
    }

    public function store(CreateEditGiftRequest $request)
    {
        $attributes = $request->all();
        $gift       = $this->repository->create($attributes);

        return redirect()->route('admin.gift.index')
            ->with('successMessage', 'Quà tặng đã được thêm mới thành công');
    }

    public function show($id)
    {
        $page_title            = '10.4.1 Sửa đại lý';
        $formOptions           = $this->service->formOptions($this->repository->findOrFail($id));
        $formOptions['action'] = route('admin.gift.update', $id);
        $default_values        = $formOptions['default_values'] ?? [];

        $view_data = compact('formOptions', 'default_values', 'page_title');

        return view('pages.gift.create-or-edit', $view_data);
    }

    public function update(CreateEditGiftRequest $request, $id)
    {
        $attributes = $request->all();
        $this->service->update($id, $attributes);

        return redirect()->route('admin.gift.index')
            ->with('successMessage', 'Quà Tặng đã được cập nhập thành công');
    }

    public function destroy($id)
    {
        try {
            $this->repository->delete($id);

            return response()->json([
                'message' => 'Quà tặng đã được xóa thành công!'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }
}
