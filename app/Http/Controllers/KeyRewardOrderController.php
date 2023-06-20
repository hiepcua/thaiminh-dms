<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderBulkAction;
use App\Repositories\KeyRewardOrder\KeyRewardOrderRepositoryInterface;
use App\Services\KeyRewardOrderService;
use Illuminate\Http\Request;

class KeyRewardOrderController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(
        KeyRewardOrderRepositoryInterface $repository,
        KeyRewardOrderService             $service
    )
    {
        $this->repository = $repository;
        $this->service    = $service;
    }

    public function index(Request $request)
    {
        $page_title = 'KeyRewardOrder';

        $indexOptions = $this->service->indexOptions();
        $search       = $request->get('search', []);
//        dd($search);
        $table = $this->service->getDataTable($search, $request->get('options', []));

        return view('pages.key_reward_order.list', compact('indexOptions', 'search', 'table'));
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

    public function orderDetail($id)
    {
        $pharmacy_info = $this->repository->getInfoPharmacy($id);
        $table         = $this->service->getTableDetailData($id, [
        ]);

        return view('pages.reports.revenue_pharmacy.detail', compact('table', 'pharmacy_info'));
    }

    public function change_status(StoreOrderBulkAction $request): \Illuminate\Http\RedirectResponse
    {
        $this->service->change_status($request->get('store_ids'));

        return redirect()->back()
            ->with('successMessage', 'Đơn hàng đã được cập nhật.');
    }

    public function exportKeyRewardOrder(Request $request)
    {
        $showOptions = $request->get('options', [
            "orderBy" => ["column" => "report_revenue_store.id", "type" => "DESC"]
        ]);
        return $this->service->exportKeyReward(
            $request->get('hash_id', null),
            $request->get('search', []),
            $showOptions
        );
    }
}
