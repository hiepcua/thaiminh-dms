<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Http\Requests\ChangeStatusForgetChecinRequest;
use App\Http\Requests\CreateForgetCheckinRequest;
use App\Repositories\ForgetCheckin\ForgetCheckinRepositoryInterface;
use App\Services\ForgetCheckinService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ForgetCheckinController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(
        ForgetCheckinRepositoryInterface $repository,
        ForgetCheckinService             $service
    )
    {
        $this->middleware('can:tao_de_xuat_quen_checkout')->only('create');
        $this->middleware('can:tao_de_xuat_quen_checkout')->only('store');
        $this->middleware('can:duyet_de_xuat_quen_checkout')->only('updateStatus');

        $this->repository = $repository;
        $this->service    = $service;
    }

    public function index(Request $request)
    {
        $page_title = 'ForgetCheckin';
        $search     = $request->get('search', []);
        $results    = $this->repository->paginate(20, [], $search);
    }

    public function create()
    {
        $page_title            = '5.3.1 Tạo đề xuất';
        $formOptions           = $this->service->formOptions();
        $formOptions['action'] = route('admin.checkin.forget-checkout.store');
        $default_values        = $formOptions['default_values'] ?? [];
        $view_data             = compact('formOptions', 'default_values', 'page_title');

        return view('pages.checkin.create-or-edit', $view_data);
    }

    public function store(CreateForgetCheckinRequest $request)
    {
        $attributes = $request->all();

        $result = $this->service->createByASM($attributes);

        if ($result['result']) {
            return redirect()->route('admin.checkin.histories')->with('successMessage', $result['message']);
        }

        return redirect()->route('admin.checkin.histories')->with('errorMessage', $result['message']);
    }

    public function show($id)
    {
        $item = $this->repository->find($id);
    }

    public function edit($id)
    {
        $item = $this->repository->find($id);
    }

    public function update(Request $request, $id)
    {
        $attributes = $request->all();
        $item       = $this->repository->update($id, $attributes);
    }

    public function destroy($id)
    {
        $this->repository->delete($id);
    }

    public function updateStatus(ChangeStatusForgetChecinRequest $request)
    {
        try {
            $id           = $request->get('id', null);
            $reviewerNote = $request->get('reviewerNote', null);
            $status       = $request->get('status', null);

            $result = $this->service->changeStatus($id, $status, $reviewerNote);

            if (!$result['result']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Có lỗi xảy ra'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
                'message' => $result['message'] ?? 'Duyệt đề xuất thành công'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }
}
