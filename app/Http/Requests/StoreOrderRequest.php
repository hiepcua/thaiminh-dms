<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use App\Models\Organization;
use App\Models\Promotion;
use App\Models\StoreOrder;
use App\Repositories\Agency\AgencyRepositoryInterface;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ProductGroupPriority\ProductGroupPriorityRepositoryInterface;
use App\Repositories\Promotion\PromotionRepositoryInterface;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Services\ReportRevenueStoreRankService;
use App\Services\StoreOrderService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function __construct(
        protected StoreOrderService                       $storeOrderService,
        protected OrganizationRepositoryInterface         $organizationRepository,
        protected StoreRepositoryInterface                $storeRepository,
        protected AgencyRepositoryInterface               $agencyRepository,
        protected ProductRepositoryInterface              $productRepository,
        protected ProductGroupPriorityRepositoryInterface $productGroupPriorityRepository,
        protected PromotionRepositoryInterface            $promotionRepository,
        protected ReportRevenueStoreRankService           $reportRevenueStoreRankService,
        array                                             $query = [],
        array                                             $request = [],
        array                                             $attributes = [],
        array                                             $cookies = [],
        array                                             $files = [],
        array                                             $server = [],
                                                          $content = null
    )
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Helper::userCan('them_don_hang_nha_thuoc');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $organizationId   = $attributes['organization_id'] ?? 0;
        $userOrganization = Helper::getUserOrganization();
        $organization     = $organizationId ? $this->organizationRepository->find($organizationId) : null;

        $rules = [
            'booking_at'       => ['required'],
            'organization_id'  => ['required'],
            'agency_id'        => [
                Rule::requiredIf(function () {
                    $orderType = $this->request->get('order_type', StoreOrder::ORDER_TYPE_DON_THUONG);
                    return $orderType == StoreOrder::ORDER_TYPE_DON_TTKEY;
                }),
                function ($attribute, $value, $fail) use ($organization, $userOrganization) {
                    if ($value) {
                        $agency              = $this->agencyRepository->find($value, ['organizations']);
                        $agencyOrganizations = $agency ? $agency->organizations->pluck('id')->toArray() : [];

                        if (!$agency) {
                            $fail ('Đại lý không còn hoạt động.');
                        } elseif ($organization && !in_array($organization->id, $agencyOrganizations)) {
                            $fail ('Đại lý không còn ở địa bàn ' . $organization->name);
                        } elseif ($userOrganization && !array_intersect($agencyOrganizations, $userOrganization[Organization::TYPE_DIA_BAN])) {
                            $fail('Đại lý không còn ở địa bàn của TDV');
                        }
                    }
                }
            ],
            'store_id'         => [
                'required',
                function ($attribute, $value, $fail) use ($organization, $userOrganization) {
                    if ($value) {
                        $store = $this->storeRepository->find($value);
                        if (!$store) {
                            $fail('Nhà thuốc không còn hoạt động.');
                        } elseif ($organization && $store->organization_id != $organization->id) {
                            $fail('Nhà thuốc không còn ở địa bàn ' . $organization->name);
                        } elseif ($userOrganization && !in_array($store->organization_id, $userOrganization[Organization::TYPE_DIA_BAN])) {
                            $fail('Nhà thuốc không còn ở địa bàn của TDV');
                        }
                    }
                }
            ],
            'note'             => ['nullable', 'string'],
            'products'         => ['required', function ($attribute, $value, $fail) {
                if ($value) {
                    $currentUser = Helper::currentUser();
                    $productIds  = collect($value)->where('type', 'product')->pluck('id')->unique()->toArray();
                    $products    = $this->productRepository->getByArrId($productIds);

                    $diffValues = array_diff($productIds, $products->pluck('id')->toArray());
                    if ($diffValues) {
                        $productInactive = $this->productRepository->getByArrId($diffValues, [], false);

                        $fail('Sản phẩm không còn hoạt động: ' . $productInactive->pluck('name')->join(', '));
                    } elseif ($currentUser->product_groups->isNotEmpty()) {
                        $currentDate       = now()->format('Y-m-d');
                        $productPriorities = $this->productGroupPriorityRepository->getList([], ['minDate' => $currentDate, 'maxDate' => $currentDate]);
                        $productPriorities = collect($productPriorities);

                        $tmpProductIds  = $productPriorities->whereIn('group_id', $currentUser->product_groups->pluck('id')->toArray())
                            ->pluck('product_id')->toArray();
                        $userProducts   = $this->productRepository->getByArrId($tmpProductIds);
                        $userProductIds = $userProducts->pluck('id')->toArray();

                        $diffValues = array_diff($productIds, $userProductIds);
                        if ($diffValues) {
                            $productInactive = $this->productRepository->getByArrId($diffValues, [], false);

                            $fail('Cấu hình sản phẩm bị thay đổi: ' . $productInactive->pluck('name')->join(', '));
                        }
                    }
                }
            }],
            'products.*.name'  => ['required', 'string'],
            'products.*.price' => ['required', 'string'],
            'products.*.qty'   => ['required', 'string', 'min:1'],
            'promotions'       => [function ($attribute, $value, $fail) use ($userOrganization) {
                if ($value) {
                    $value        = array_filter($value);
                    $promotions   = $this->promotionRepository->getByArrId(array_keys($value));
                    $activePromos = $promotions->filter(function ($item) {
                        return $item->status == Promotion::STATUS_ACTIVE;
                    });
                    $activeIds    = $activePromos->pluck('id')->toArray();

                    $diffValues = array_diff(array_keys($value), $activeIds);
                    if ($diffValues) {
                        $fail('CTKM không còn hoạt động: ' . $promotions->whereIn('id', $diffValues)->join(', '));
                    }
                }
            }],
            'order_type'       => [

            ],
            'order_logistic'   => [
                function ($attribute, $value, $fail) {
                    if ($value && $value == StoreOrder::ORDER_LOGISTIC_VIETTEL) {
                        $storeId = $this->request->get('store_id');
                        if ($storeId && $store = $this->storeRepository->find($storeId)) {
                            $products    = $this->request->get('products', []);
                            $productType = $this->request->get('product_type', 0);
                            $bonusDetail = $this->reportRevenueStoreRankService->getCurrentBonusOfStore($storeId, $store->type, $productType);
                            $totalBonus  = $bonusDetail['total_bonus'] ?? 0;
                            $orderAmount = collect($products)->filter(function ($_product) {
                                return $_product['type'] == 'product';
                            })->map(function ($_product) {
                                $_product['_amount'] = $_product['price'] * $_product['qty'];
                                return $_product;
                            })->sum('_amount');
                            if ($orderAmount < $totalBonus || $orderAmount > ($totalBonus + 20000)) {
//                                $fail('test');
                            }
                        }
                    }
                }
            ],
        ];
        if (Helper::isTDV()) {
            unset($rules['booking_at']);
            unset($rules['organization_id']);
        }
        return $rules;
    }

    public function withValidator(Validator $validator)
    {
        $attributes     = [
            'products'   => $this->request->get('products', []),
            'promotions' => $this->request->get('promotions', []),
        ];
        $products       = collect($attributes['products']);
        $isPromoProduct = $products->where('promo_id', '>', 0)->count();
        if ($isPromoProduct) {
            $promoValues = $this->storeOrderService->getPromotionValues($attributes);
            $promoItems  = [];
            foreach ($promoValues as $promoValue) {
                foreach ($promoValue['data']['items'] as $_key => $item) {
                    $promoItems[$_key] = $item;
                }
            }

            $requestPromoItems = $products->where('promo_id', '>', 0)->map(function ($item) {
                return $this->storeOrderService->_checkItemValue($item);
            })->toArray();

            foreach ($requestPromoItems as $itemKey => $itemValue) {
                $isValid = true;
                if (!isset($promoItems[$itemKey])) {
                    $isValid = false;
                } else {
                    $diff = collect($itemValue)->diffAssoc(collect($promoItems[$itemKey]));
                    if ($diff->count()) {
                        $isValid = false;
                    }
                }
                if (!$isValid) {
                    $validator->after(function ($validator) use ($itemValue) {
                        $validator->errors()->add($itemValue['key'], $itemValue['promo_name'] . ' có thay đổi, bạn hãy áp dụng lại CTKM.');
                    });
                    break;
                }
            }
        }
    }

    public function attributes()
    {
        return [
            'booking_at'      => 'Ngày nhập hàng',
            'organization_id' => 'Địa bàn',
            'agency_id'       => 'Đại lý',
            'store_id'        => 'Nhà thuốc',
            'note'            => 'Ghi chú',
            'products'        => 'Sản phẩm',
        ];
    }
}
