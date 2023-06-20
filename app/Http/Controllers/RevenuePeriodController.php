<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Requests\RevenuePeriodRequest;
use App\Models\ProductGroup;
use App\Repositories\RevenuePeriod\RevenuePeriodRepositoryInterface;
use App\Services\RevenuePeriodService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RevenuePeriodController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(RevenuePeriodRepositoryInterface $repository, RevenuePeriodService $service)
    {
        $this->repository = $repository;
        $this->service    = $service;

        $this->middleware('can:xem_ql_hop_dong_key')->only('index');
        $this->middleware('can:them_ql_hop_dong_key')->only('create', 'store');
        $this->middleware('can:sua_ql_hop_dong_key')->only('edit', 'update');
    }

    public function index(Request $request)
    {
        $productTypes = ProductGroup::PRODUCT_TYPES;
        //$page_title  = '2.1 QL hợp đồng KEY';
        $formOptions = $this->service->formOptions();
        $search      = $request->get('search', []);
        //dd($search);
        $results = $this->repository->paginate(20, ['rank'], compact('search'));
        $results = $this->repository->getData(['rank'], compact('search'));

        $results->map(function ($_item) use ($productTypes) {
            $productType              = $_item->product_type;
            $productTypeName          = $productTypes[$productType]['text'] ?? '';
            $_item->product_type_name = ($productTypeName) ? "<b>($productTypeName)</b>" : '';
            $periodOfYear             = $productTypes[$productType]['period_of_year'] ?? 6;

            if (!$_item->period_to) {
                $_item->period_to = "Mãi mãi";
            } else {
                $_item->period_to = Helper::getPeriodByDate($_item->period_to, $periodOfYear);
            }

            $_item->period_from = Helper::getPeriodByDate($_item->period_from, $periodOfYear);

            $_item->group = $_item->period_from . " - " . $_item->period_to . " (<b>$productTypeName</b>)";
            return $_item;
        });

        $results = $results->groupBy('group');

        $periods = $this->getPeriodByProductType($productTypes);

        return view('pages.revenue-periods.index', compact('formOptions', 'search', 'results', 'periods'));
    }

    public function create()
    {
        $productTypes          = ProductGroup::PRODUCT_TYPES;
        $page_title            = '2.1 Thêm cấu hình';
        $formOptions           = $this->service->formOptions();
        $formOptions['action'] = route('admin.revenue-periods.store');
        $default_values        = $formOptions['default_values'];
        $rev_period_id         = false;
        $canEdit               = true;

        $periods = $this->getPeriodByProductType($productTypes);

        //Remove start_period time less than today
        foreach ($periods as $productType => $_periodData) {
            foreach ($_periodData as $_index => $_data) {
                $date = Carbon::parse(date("Y-m-d"))->subMonth(2)->firstOfMonth()->toDateString();
                if (strtotime($_data["started_at"]) <= strtotime($date)) {
                    unset($periods[$productType][$_index]);
                }

            }
        }

        return view('pages.revenue-periods.add-edit', compact(
            'page_title',
            'formOptions',
            'default_values',
            'rev_period_id',
            'productTypes',
            'periods',
            'canEdit'
        ));
    }

    private function getPeriodByProductType($productTypes)
    {
        $productTypePeriods = [];
        foreach ($productTypes as $_id => $_value) {
            $periodOfYear = $_value['period_of_year'];
            //$periodOfYear = 12;
            $productTypePeriods[$_id] = Helper::periodOptions(null, $periodOfYear);
            //return $productTypePeriods;
        }
        return $productTypePeriods;
    }

    public function store(RevenuePeriodRequest $request)
    {
        $attributes = $request->all();
        $result     = $this->service->create($attributes);

        if (!$result['status']) {
            return redirect()->back()->with('errorMessage', $result['message'])->withInput($attributes);
        }

        return redirect(route('admin.revenue-periods.index'))->with('successMessage', $result['message']);
    }

    public function edit($rev_period_id)
    {
        $productTypes          = ProductGroup::PRODUCT_TYPES;
        $page_title            = '2.1 Sửa cấu hình';
        $rev_period            = $this->repository->findOrFail($rev_period_id, ['items']);
        $canEdit               = $rev_period?->period_to == null
            || $rev_period?->period_to >= Carbon::now()->format('Y-m-d');
        $formOptions           = $this->service->formOptions($rev_period);
        $formOptions['action'] = route('admin.revenue-periods.update', $rev_period_id);
        $default_values        = $formOptions['default_values'];
        $periods               = $this->getPeriodByProductType($productTypes);
        $inGroups              = array_keys($default_values['items']);
//dd(array_keys($items));
        return view('pages.revenue-periods.add-edit', compact(
            'page_title',
            'formOptions',
            'default_values',
            'rev_period_id',
            'productTypes',
            'periods',
            'inGroups',
            'canEdit'
        ));
    }

    public function update(RevenuePeriodRequest $request, $id)
    {
        $rev_period = $this->repository->findOrFail($id, ['items']);
        $canEdit    = $rev_period?->period_to == null
            || $rev_period?->period_to >= Carbon::now()->format('Y-m-d');
        if (!$canEdit) {
            return redirect()->back()->with('errorMessage', 'Hợp đồng key đã quá hạn không được phép cập nhập');
        }
        $attributes = $request->all();
        //dd($attributes);
        $result = $this->service->update($id, $attributes);

        if (!$result['status']) {
            return redirect()->back()->with('errorMessage', $result['message'])->withInput($attributes);
        }

        return redirect(route('admin.revenue-periods.index'))->with('successMessage', $result['message']);
    }

    public function destroy($id)
    {
//        $this->repository->delete($id);
        return redirect(route('admin.revenue-periods.index'));
    }

    public function findProductByDate(Request $request)
    {

        return response()->json($this->service->findProductByDate($request->all()));
    }
}
