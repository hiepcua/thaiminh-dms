<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Requests\StoreOrderBulkAction;
use App\Http\Requests\StoreOrderGetPromotion;
use App\Http\Requests\StoreOrderRequest;
use App\Models\StoreOrder;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Repositories\StoreOrder\StoreOrderRepositoryInterface;
use App\Services\StoreOrderService;
use Illuminate\Http\Request;

class StoreOrderController extends Controller
{
    public function __construct(
        protected StoreOrderRepositoryInterface $repository,
        protected StoreOrderService             $service,
        protected StoreRepositoryInterface      $storeRepository,
    )
    {
        $this->middleware('can:xem_don_hang_nha_thuoc')->only('index');
        $this->middleware('can:them_don_hang_nha_thuoc')->only('create', 'store');
    }

    public function index(Request $request)
    {
        $indexOptions = $this->service->indexOptions();
        $search       = $request->get('search', []);
        $table        = $this->service->getTable($search, $request->get('options', []));
        $isAddNew     = Helper::userCan('them_don_hang_nha_thuoc');

//        $this->service->createParentTTKey($this->repository->find(1000863153));

        return view('pages.store_orders.index', compact('indexOptions', 'search', 'table', 'isAddNew'));
    }

    public function create()
    {
        $order_id              = 0;
        $formOptions           = $this->service->formOptions();
        $formOptions['action'] = route('admin.store-orders.store');
        $formOptions['is_tdv'] = Helper::isTDV();
        $default_values        = $formOptions['default_values'];
        return view('pages.store_orders.add-edit', compact('order_id', 'formOptions', 'default_values'));
    }

    public function store(StoreOrderRequest $request)
    {
        $attributes = $request->all();

        $storeOrder = $this->service->create($attributes);
        if ($storeOrder->order_type == StoreOrder::ORDER_TYPE_DON_TTKEY && $storeOrder->parent_id == -1) {
            $this->service->createParentTTKey($storeOrder);
        }

        return redirect()->route('admin.store-orders.index')
            ->with('successMessage', 'Đơn hàng đã được tạo.');
    }

    public function show($id)
    {
//        $item = $this->repository->find($id);
    }

    public function edit($id)
    {
//        $item = $this->repository->find($id);
    }

    public function update(Request $request, $id)
    {
//        $attributes = $request->all();
//        $item       = $this->repository->update($id, $attributes);
    }

    public function destroy($id)
    {
//        $this->repository->delete($id);
        return redirect()->route('admin.store-orders.index');
    }

    public function getDataByLocality(Request $request)
    {
        $defaultValues = $request->all();
        $locality_id   = $request->get('organization_id');
        $store_id      = $request->get('store_id');
        if (!$locality_id && $store_id) {
            $store       = $this->storeRepository->find($store_id);
            $locality_id = $store->organization_id;
        }

        $output = $this->service->getDataByLocality($locality_id, $defaultValues);
        return response()->json($output);
    }

    function getPromotionItems(StoreOrderGetPromotion $request): \Illuminate\Http\JsonResponse
    {
        $promoValues = $this->service->getPromotionValues($request->all());
        return response()->json($promoValues);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function order_action(StoreOrderBulkAction $request, $type): \Illuminate\Http\RedirectResponse
    {
        $this->service->order_action($type, $request->get('store_ids'));

        return redirect()->back()
            ->with('successMessage', 'Đơn hàng đã được cập nhật.');
    }
}
