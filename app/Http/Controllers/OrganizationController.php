<?php

namespace App\Http\Controllers;

use App\Helpers\ApiHelper;
use App\Http\Requests\CreateEditOrganization;
use App\Http\Requests\DeleteOrganization;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Services\AgencyService;
use App\Services\OrganizationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrganizationController extends Controller
{
    protected $repository;
    protected $service;
    protected $agencyService;

    public function __construct(
        OrganizationRepositoryInterface $repository,
        OrganizationService             $service,
        AgencyService                   $agencyService
    )
    {
        $this->repository    = $repository;
        $this->service       = $service;
        $this->agencyService = $agencyService;

        $this->middleware('can:xem_cay_so_do')->only('index');
        $this->middleware('can:them_cay_so_do')->only('create', 'store');
        $this->middleware('can:sua_cay_so_do')->only('edit', 'update');
        $this->middleware('can:xoa_cay_so_do')->only('destroy');
    }

    public function index(Request $request)
    {
        $formOptions = [
            'types' => $this->repository->getModel()::TYPE_TEXTS,
        ];
        $results     = $this->service->dataLists($request);

        return view('pages.organizations.index', compact('formOptions', 'results'));
    }

    public function create()
    {
        $organization          = $this->repository->getModel();
        $organization_id       = $organization->id;
        $formOptions           = $this->repository->formOptions();
        $formOptions['action'] = route('admin.organizations.store');
        $default_values        = $formOptions['default_values'];

        return view('pages.organizations.add-edit', compact('organization_id', 'formOptions', 'default_values'));
    }

    public function store(CreateEditOrganization $request)
    {
        $attributes = $request->all();
        $this->service->create($attributes);

        return redirect(route('admin.organizations.index'));
    }

    public function show($id)
    {
//        $item = $this->repository->find($id);
    }

    public function edit($organization_id)
    {
        $organization          = $this->repository->findOrFail($organization_id, ['districts']);
        $formOptions           = $this->repository->formOptions($organization);
        $formOptions['action'] = route('admin.organizations.update', $organization_id);
        $default_values        = $formOptions['default_values'];

        return view('pages.organizations.add-edit', compact('organization_id', 'formOptions', 'default_values'));
    }

    public function update(CreateEditOrganization $request, $id)
    {
        $attributes = $request->all();
        $this->service->update($id, $attributes);

        return redirect(route('admin.organizations.index'));
    }

    public function destroy(DeleteOrganization $request, $organization_id)
    {
        $organization = $this->repository->findOrFail($organization_id);
        $this->repository->delete($organization_id);

        return redirect(route('admin.organizations.index'))
            ->with([
                'successMessage' => $organization->name . ' đã được xóa.',
            ]);
    }

    public function getLocality(Request $request)
    {
        try {
            return response()->json([
                'htmlString' => $this->service->getOptionLocalityByDivision($request->get('division_id', null))
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAgency(Request $request)
    {
        try {
            return response()->json([
                'htmlString' => $this->agencyService->getOptionAgencyByLocality($request->get('locality_id', null))
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function getLocalityUser(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            return response()->json([
                'htmlString' => $this->service->getOptionUserByLocality($request->get('locality_id'))
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function getLocalityByProvince(Request $request)
    {
        try {
            return response()->json([
                'htmlString' => $this->service->getOptionLocalityByProvince($request->get('province_id', default: null)),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function getUserByLocality(Request $request)
    {
        try {
            return response()->json([
                'htmlString' => $this->service->getUserByLocality($request->get('locality_id', default: null)),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }

    public function getUserByDivision(Request $request)
    {
        try {
            return response()->json([
                'htmlString' => $this->service->getUserByDivision($request->get('division_id', default: null)),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $e->methordError = __METHOD__;

            return ApiHelper::responseError(error: $e);
        }
    }
}
