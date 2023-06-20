<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Http\Requests\CreateEditAgencyRequest;
use App\Models\Organization;
use App\Repositories\Agency\AgencyRepositoryInterface;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Services\AgencyService;
use App\Services\OrganizationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AgencyController extends Controller
{
    protected $repository;
    protected $organizationService;
    protected $service;

    public function __construct(
        AgencyRepositoryInterface $repository,
        AgencyService             $service,
        OrganizationService       $organizationService,
    )
    {
        $this->repository          = $repository;
        $this->organizationService = $organizationService;
        $this->service             = $service;

        $this->middleware('permission:xem_danh_sach_dai_ly')->only('index');
        $this->middleware('permission:them_dai_ly')->only('create', 'store');
        $this->middleware('permission:sua_dai_ly')->only('show', 'edit');
        $this->middleware('permission:xoa_dai_ly')->only('destroy');
    }

    public function index(Request $request)
    {
        $page_title  = '3.1 Danh sách đại lý';
        $search      = $request->get('search', []);
        $showOptions = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "agencies.created_at", "type" => "DESC"]]
        ]);

        $formOptions   = $this->service->formOptions();
        $defaultValues = $formOptions['default_values'] ?? [];
        $agenciesTable = $this->service->getTable($search, $showOptions);

        return view('pages.agency.index', compact('agenciesTable', 'page_title', 'formOptions', 'defaultValues'));
    }

    public function create()
    {
        $page_title            = '3.1 Thêm đại lý';
        $agencyId              = 0;
        $formOptions           = $this->service->formOptions();
        $formOptions['action'] = route('admin.agency.store');
        $default_values        = $formOptions['default_values'] ?? [];
        $view_data             = compact('formOptions', 'agencyId', 'default_values', 'page_title');

        return view('pages.agency.create-or-edit', $view_data);
    }

    public function store(CreateEditAgencyRequest $request)
    {
        $attributes = $request->all();
        $this->service->create($attributes);

        return redirect()->route('admin.agency.index')
            ->with('successMessage', 'Địa bàn đã được thêm mới thành công');
    }

    public function show($agencyId)
    {
        $page_title            = '3.1 Sửa đại lý';
        $formOptions           = $this->service->formOptions($this->repository->findOrFail($agencyId));
        $formOptions['action'] = route('admin.agency.update', $agencyId);
        $default_values        = $formOptions['default_values'] ?? [];
        $view_data             = compact('agencyId', 'formOptions', 'default_values', 'page_title');

        return view('pages.agency.create-or-edit', $view_data);
    }

    public function update(CreateEditAgencyRequest $request, $id)
    {
        $attributes = $request->all();
        $this->service->update($id, $attributes);

        return redirect()->route('admin.agency.index')
            ->with('successMessage', 'Địa bàn đã được cập nhập thành công');
    }

    public function destroy($id)
    {
        try {
            $result = $this->service->deleteAgency($id);

            if (!$result['result']) {
                response()->json([
                    'message' => $result['message'] ?? ''
                ], $result['status'] ?? Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
                'message' => $result['message'] ?? ''
            ], $result['status'] ?? Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function getByLocality(Request $request)
    {
        try {
            $locality = $request->get('locality', null);

            if ($locality) {
                $agencies = $this->repository->getByLocality($locality);

                return response()->json([
                    'result'   => true,
                    'agencies' => $agencies
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'message' => 'Địa bàn không được để trống.'
                ], Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function getProvinces(Request $request)
    {
        try {
            $divisionId = $request->get('locality_ids');

            $result = array_merge(
                $this->organizationService->getProvinceByLocalities($divisionId),
                $this->organizationService->getDisctricByLocalities($divisionId)
            );

            return response()->json([
                'provinces' => $result
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function getAgencyCode(Request $request): \Illuminate\Http\JsonResponse
    {
        $codePrefix = $request->get('codePrefix');

        if (!$codePrefix) {
            return ApiHelper::responseError();
        }

        return response()->json(['code' => $this->service->generateCode($codePrefix)]);
    }
}
