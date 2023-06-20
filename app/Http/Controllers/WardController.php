<?php

namespace App\Http\Controllers;

use App\Repositories\Ward\WardRepositoryInterface;
use Illuminate\Http\Request;

class WardController extends Controller
{
    protected $repository;

    public function __construct(WardRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $page_title = 'Ward';
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
