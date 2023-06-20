<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Repositories\Rank\RankRepositoryInterface;
use App\Services\RankService;
use Illuminate\Http\Request;

class RankController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(RankRepositoryInterface $repository,
        RankService $service
    ){
        $this->repository = $repository;
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $page_title = '2.1 Quản lý hạng';
        $search     = $request->get('search', []);
        $results    = $this->repository->paginate(20, [], $search);

        return view('pages.ranks.index', compact(
            'page_title',
            'results',
        ));
    }

    public function create()
    {
        $page_title             = '1.6 Thêm hạng';
        $formOptions            = $this->repository->formOptions();
        $formOptions['action']  = route('admin.ranks.store');
        $default_values         = $formOptions['default_values'];
        $rank_id                = '';

        return view('pages.ranks.add-edit', compact(
            'page_title',
            'formOptions',
            'default_values',
            'rank_id'
        ));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
        ];

        $messages = [
            'name.required' => 'Tên hạng là bắt buộc.',
        ];

        $validator = $this->validate($request, $rules, $messages);
        $request->name = Helper::convertSpecialCharInput($request->name);

        if ($validator) {
            $mess = [];
            $attributes = $request->all();
            $result = $this->repository->create($attributes);
            if($result){
                $mess['type'] = 'success';
                $mess['content'] = 'Thêm mới hạng thành công';
                return redirect()->route('admin.ranks.index')->with('messages', $mess);
            }else{
                $mess['type'] = 'error';
                $mess['content'] = 'Thêm mới hạng lỗi';
                return redirect()->route('admin.ranks.create')->with('messages', $mess);
            }
        } else {
            return back()->withErrors($validator)->withInput();
        }
    }

    public function show($id)
    {
        $item = $this->repository->find($id);
    }

    public function edit($id)
    {
        $item                   = $this->repository->find($id);
        $rank_id                = $item->id;
        $page_title             = '1.6 Sửa hạng';
        $formOptions            = $this->repository->formOptions($item);
        $formOptions['action']  = route('admin.ranks.update', $rank_id);
        $default_values         = $formOptions['default_values'];

        return view('pages.ranks.add-edit', compact(
            'page_title',
            'formOptions',
            'default_values',
            'rank_id'
        ));

    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required',
        ];

        $messages = [
            'name.required' => 'Tên hạng là bắt buộc.',
        ];

        $validator = $this->validate($request, $rules, $messages);
        $request->name = Helper::convertSpecialCharInput($request->name);

        if ($validator) {
            $attributes = $request->all();
            $updateResult = $this->service->update($id, $attributes);

            if($updateResult['result']){
                return redirect()->route('admin.ranks.index')->with('successMessage', $updateResult['message']);
            }

            return redirect()->route('admin.ranks.index')->with('errorMessage', $updateResult['message']);
        } else {
            return back()->withErrors($validator)->withInput();
        }
    }

    public function destroy($id)
    {
        $this->repository->delete($id);
    }
}
