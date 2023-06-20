<?php

namespace App\Http\Controllers;

use App\Repositories\LineGroup\LineGroupRepositoryInterface;
use App\Services\LineGroupService;
use Illuminate\Http\Request;

class LineGroupController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(
        LineGroupRepositoryInterface $repository,
        LineGroupService $service
    ) {
        $this->repository = $repository;
        $this->service    = $service;
    }

    public function index(Request $request)
    {
        $page_title = 'LineGroup';
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
}
