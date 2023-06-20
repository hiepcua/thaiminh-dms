<?php

namespace App\Http\Controllers;

use App\Repositories\SystemVariable\SystemVariableRepositoryInterface;
use App\Services\SystemVariableService;
use Illuminate\Http\Request;

class SystemVariableController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(
        SystemVariableRepositoryInterface $repository,
        SystemVariableService $service
    ) {
        $this->repository = $repository;
        $this->service    = $service;

        $this->middleware('permission:setting_thong_so_he_thong')->only('setting');
        $this->middleware('permission:setting_thong_so_he_thong')->only('update');
    }

    public function setting()
    {
        $functionVariables = $this->service->getVariables();

        return view('pages.system_variable.setting', compact('functionVariables'));
    }

    public function update(Request $request)
    {
        $result = $this->service->update($request->get('variables', []));

        if ($result) {
            return redirect()->back()->with('successMessage', 'Cập nhập thành công');
        }

        return redirect()->back()->with('errorMessage', 'Cập nhập thất bại');
    }
}
