<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Repositories\Checkin\CheckinRepositoryInterface;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Services\CheckinService;
use App\Services\StoreService;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    protected $repository;
    protected $service;
    protected $storeRepository;

    public function __construct(
        CheckinRepositoryInterface $repository,
        CheckinService $service,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->repository = $repository;
        $this->service    = $service;
        $this->storeRepository = $storeRepository;

        $this->middleware('can:xem_danh_sach_tdv_checkin')->only('history');
    }

    public function index(Request $request)
    {
        $page_title = 'Checkin';
        $search     = $request->get('search', []);
        $results    = $this->repository->paginate(20, [], $search);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $attributes = $request->all();
        $item       = $this->repository->create($attributes);
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

    public function history(Request $request)
    {
        $page_title  = '5.3.1 Lịch sử checkin';
        $search        = $request->get('search', []);
        $showOptions   = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "checkin.created_at", "type" => "DESC"]]
        ]);

        $showOptionsOfRequestTab   = $request->get('options', [
            "perPage" => config("table.default_paginate"),
            "orderBy" => [["column" => "forget_checkins.created_at", "type" => "DESC"]]
        ]);

        $table = $this->service->getTable($search, $showOptions);
        $tableRequestCheckin = $this->service->getTableRequestTab($search, $showOptionsOfRequestTab);

        return view('pages.checkin.history', compact(
            'table',
            'page_title',
            'tableRequestCheckin'
        ));
    }
}
