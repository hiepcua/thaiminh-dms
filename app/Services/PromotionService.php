<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\OrganizationPromotion;
use App\Models\PromotionCondition;
use App\Models\Product;
use App\Repositories\Gift\GiftRepositoryInterface;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ProductGroup\ProductGroupRepositoryInterface;
use App\Repositories\ProductGroupPriority\ProductGroupPriorityRepository;
use App\Repositories\Promotion\PromotionRepositoryInterface;
use App\Repositories\PromotionCondition\PromotionConditionRepositoryInterface;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromotionService extends BaseService
{
    protected $repository;
    protected $organizationRepository;
    protected $productRepository;
    protected $giftRepository;
    protected $promotionConditionRepository;
    protected $productGroupRepository;
    protected $productGroupPriorityRepository;

    public function __construct(
        PromotionRepositoryInterface          $repository,
        OrganizationRepositoryInterface       $organizationRepository,
        ProductRepositoryInterface            $productRepository,
        GiftRepositoryInterface               $giftRepository,
        PromotionConditionRepositoryInterface $promotionConditionRepository,
        ProductGroupRepositoryInterface       $productGroupRepository,
        ProductGroupPriorityRepository        $productGroupPriorityRepository
    )
    {
        parent::__construct();

        $this->repository                     = $repository;
        $this->organizationRepository         = $organizationRepository;
        $this->productRepository              = $productRepository;
        $this->giftRepository                 = $giftRepository;
        $this->promotionConditionRepository   = $promotionConditionRepository;
        $this->productGroupRepository         = $productGroupRepository;
        $this->productGroupPriorityRepository = $productGroupPriorityRepository;
    }

    public function setModel()
    {
        return new Promotion();
    }

    public function getDataForScreenList($requestParams)
    {
        $promotions = $this->repository->getByRequest(
            20,
            ['createdBy', 'divisions'],
            $requestParams,
        );

        return $promotions;
    }

    public function formOptions($model = null): array
    {
        $options             = parent::formOptions($model);
        $options['types']    = PromotionCondition::TYPE_TEXTS;
        $options['status']   = Promotion::STATUS_TEXTS;
        $options['products'] = $this->productRepository->getActiveProduct()->pluck('name', 'id')->toArray();
        $productGroups       = $this->productGroupRepository->getSubGroupActive();
        $productGroups->map(function ($productGroup) {
            $productGroup->fullName = $productGroup->name . " (" . $productGroup->parent?->name . ")";

            return $productGroup;
        });
        $options['productGroups'] = $productGroups->pluck('fullName', 'id')->toArray();
        $options['gifts']         = $this->giftRepository->all()->sortBy('name')->pluck('name', 'id')->toArray();

        if (isset($model)) {
            $options['default_values']['division_id']         = $model->organizations()->pluck('organizations.id')->toArray();
            $options['default_values']['division_id_exclude'] = $model->organizationsExclude()->pluck('organizations.id')->toArray();
            $options['default_values']['status']              = $model->status;
            $options['default_values']['types']               = [];

            $promotionConditions = $this->promotionConditionRepository->getConditionsOfPromotion($model);
            foreach ($promotionConditions as $promotionCondition) {
                $typeCondition = $promotionCondition->type;
                $gift          = [];
                $conditions    = (array)json_decode($promotionCondition->condition);

                foreach ($conditions as $condition) {
                    $condition = (array)$condition;
                    $giftItem  = [];
                    foreach ($condition as $key => $field) {
                        $fieldData = (array)$field;
                        if ($key == 'conditions') {
                            $fieldConditionData = [];

                            foreach ($fieldData as $data) {
                                $fieldConditionData[] = (array)$data;
                            }
                            $giftItem[$key] = $fieldConditionData;
                        } else {
                            $giftItem[$key] = (array)$field;
                        }

                    }

                    $gift[] = $giftItem;
                }

                $options["default_values"]['types'][] = [
                    'type'               => "type{$typeCondition}",
                    'nameCondition'      => $promotionCondition->name,
                    "type$typeCondition" => $gift
                ];
            }
        }

        if (count(old())) {
            $options['default_values']['division_id']         = old('division_id', []);
            $options['default_values']['division_id_exclude'] = old('division_id_exclude', []);
            $options["default_values"]['types']               = [];
            foreach (old('typesPromotion', []) as $oldTypePromotion) {
                $typeCondition = isset($oldTypePromotion['type']) ? 'type' . $oldTypePromotion['type'] : null;
                $conditions    = [];

                foreach ($oldTypePromotion[$typeCondition] ?? [] as $condition) {
                    $conditions[] = [
                        'includeType' => $condition['includeType'] ?? '',
                        'excludeType' => $condition['excludeType'] ?? '',
                        'includes'    => [
                            'all'       => ($condition['includeType'] ?? '') == 'all',
                            'subGroups' => $condition['includeGroups'] ?? [],
                            'products'  => $condition['includeProducts'] ?? [],
                        ],
                        'excludes'    => [
                            'subGroups' => $condition['excludeGroups'] ?? [],
                            'products'  => $condition['excludeProducts'] ?? [],
                        ],
                        'conditions'  => $condition['gifts'] ?? []
                    ];
                }

                $options["default_values"]['types'][] = [
                    'type'          => $typeCondition,
                    'nameCondition' => $oldTypePromotion['nameCondition'] ?? '',
                    $typeCondition  => $conditions
                ];
            }
            foreach (PromotionCondition::TYPES as $type) {
                $options['default_values']["type$type"] = old("type$type");
            }
        }

        return $options;
    }

    public function create($attributes)
    {
        try {
            DB::beginTransaction();

            $attributes['started_at'] = isset($attributes['started_at']) ? ($attributes['started_at'] . ' 00:00:00') : null;
            $attributes['ended_at']   = isset($attributes['ended_at']) ? ($attributes['ended_at'] . ' 23:59:59') : null;
            $attributes['auto_apply'] = isset($attributes['auto_apply']) ?? Promotion::NO_AUTO_APPLY;
            $attributes['created_by'] = Helper::currentUser()->id;

            $promotion = $this->repository->create($attributes);

            //create promotion condition
            $this->createPromotionConditions($attributes, $promotion);

            $includeOrganization = $attributes['division_id'] ?? [];
            $pivotData           = array_fill(0, count($includeOrganization), ['type' => OrganizationPromotion::TYPE_INCLUDE]);
            $syncData            = array_combine($includeOrganization, $pivotData);
            $promotion->organizations()->sync($syncData);

            $excludeOrganization = $attributes['division_id_exclude'] ?? [];
            $pivotDataExclude    = array_fill(0, count($excludeOrganization), ['type' => OrganizationPromotion::TYPE_EXCLUDE]);
            $syncDataExclude     = array_combine($excludeOrganization, $pivotDataExclude);
            $promotion->organizationsExclude()->sync($syncDataExclude);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(__METHOD__ . " error: " . $e->getMessage());
            Log::error($e);
        }
    }


    /**
     * @param $attributes
     * @param mixed $promotion
     */
    public function createPromotionConditions($attributes, mixed $promotion)
    {
        $promotionData = $attributes['typesPromotion'] ?? [];
        foreach ($promotionData as $attribute) {
            $typePromotion = $attribute['type'] ?? null;
            $conditions    = $attribute["type$typePromotion"] ?? [];
            $newConditions = [];

            foreach ($conditions as $condition) {
                $typeIncludeProduct = $condition['includeType'] ?? '';
                $typeExcludeProduct = $condition['excludeType'] ?? '';
                $includeProducts    = [];
                $excludeProducts    = [];

                switch ($typeIncludeProduct) {
                    case '':
                        $includeProducts['all'] = true;
                        break;
                    case 'include-groups':
                        $includeProducts['subGroups'] = $condition['includeGroups'] ?? [];
                        break;
                    case 'include-products':
                        $includeProducts['products'] = $condition['includeProducts'] ?? [];
                        break;
                    default:
                        $includeProducts = [
                            'all'       => true,
                            'subGroups' => [],
                            'products'  => [],
                        ];
                        break;
                }

                switch ($typeExcludeProduct) {
                    case 'exclude-groups':
                        $excludeProducts['subGroups'] = $condition['excludeGroups'] ?? [];
                        break;
                    case 'exclude-products':
                        $excludeProducts['products'] = $condition['excludeProducts'] ?? [];
                        break;
                    default:
                        $excludeProducts = [
                            'subGroups' => [],
                            'products'  => [],
                        ];
                        break;
                }

                $newConditions[] = [
                    'includes'   => $includeProducts,
                    'excludes'   => $excludeProducts,
                    'conditions' => $condition['gifts'] ?? []
                ];
            }

            $this->promotionConditionRepository->create([
                'promotion_id' => $promotion->id,
                'name'         => $attribute['nameCondition'] ?? '',
                'type'         => $typePromotion,
                'condition'    => json_encode($newConditions)
            ]);
        }
    }

    public function update($id, $attributes)
    {
        try {
            DB::beginTransaction();

            $attributes['started_at'] = isset($attributes['started_at']) ? ($attributes['started_at'] . ' 00:00:00') : null;
            $attributes['ended_at']   = isset($attributes['ended_at']) ? ($attributes['ended_at'] . ' 23:59:59') : null;
            $promotion                = $this->repository->findOrFail($id);
            $promotion->update($attributes);
            $promotion->organizations()->detach();
            $promotion->organizationsExclude()->detach();

            //create promotion condition
            $this->promotionConditionRepository->deleteConditionOfPromotion($id);

            $this->createPromotionConditions($attributes, $promotion);

            $includeOrganization = $attributes['division_id'] ?? [];
            $pivotData           = array_fill(0, count($includeOrganization), ['type' => OrganizationPromotion::TYPE_INCLUDE]);
            $syncData            = array_combine($includeOrganization, $pivotData);
            $promotion->organizations()->sync($syncData);

            $excludeOrganization = $attributes['division_id_exclude'] ?? [];
            $pivotDataExclude    = array_fill(0, count($excludeOrganization), ['type' => OrganizationPromotion::TYPE_EXCLUDE]);
            $syncDataExclude     = array_combine($excludeOrganization, $pivotDataExclude);
            $promotion->organizationsExclude()->sync($syncDataExclude);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(__METHOD__ . " error: " . $e->getMessage());
            Log::error($e);
        }
    }

    /**
     * @param int $promotionId
     * @param int $conditionId
     * @param array $productValues
     * @return array
     */
    public function calculatePromotion(int $promotionId, int $conditionId, array $productValues): array
    {
        static $promotions;
        if (!($promotions[$promotionId] ?? false)) {
            $promotions[$promotionId] = $this->repository->find($promotionId, ['promotionConditions']);
        }
        $promotion = $promotions[$promotionId];
        $condition = $promotion?->promotionConditions->where('id', $conditionId)->first();
        if (!$condition) {
            return [];
        }
        $conditionItems = $this->parseCondition($condition, $productValues);
        if ($conditionItems->isEmpty()) {
            return [];
        }

        $values = match ($condition->type) {
            PromotionCondition::TYPE_GIFT_BY_QTY => [
                'type'  => 'gift',
                'gifts' => $this->parseGiftByQty($conditionItems)
            ],
            PromotionCondition::TYPE_GIFT_BY_TOTAL_QTY => [
                'type'  => 'gift',
                'gifts' => $this->parseGiftByTotalQty($conditionItems)
            ],
            PromotionCondition::TYPE_GIFT_BY_TOTAL_COST => [
                'type'  => 'gift',
                'gifts' => $this->parseGiftByTotalCost($conditionItems)
            ],
            PromotionCondition::TYPE_GIFT_BY_TOTAL_POINT => [
                'type'  => 'gift',
                'gifts' => $this->parseGiftByTotalPoint($conditionItems)
            ],
            PromotionCondition::TYPE_DISCOUNT_BY_QTY => [
                'type'      => 'discount',
                'discounts' => $this->parseDiscountByQty($conditionItems)
            ],
            PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_QTY => [
                'type'      => 'discount',
                'discounts' => $this->parseDiscountByTotalQty($conditionItems)
            ],
            PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_COST => [
                'type'      => 'discount',
                'discounts' => $this->parseDiscountByTotalCost($conditionItems)
            ],
            PromotionCondition::TYPE_DISCOUNT_BY_TOTAL_POINT => [
                'type'      => 'discount',
                'discounts' => $this->parseDiscountByTotalPoint($conditionItems)
            ],
            default => [],
        };
        return array_merge($values, [
            'promotion_name' => $promotion->name,
            'condition_name' => $condition->name,
        ]);
    }

    /**
     * @param $condition
     * @param $productValues
     * @return \Illuminate\Support\Collection
     */
    function parseCondition($condition, $productValues): \Illuminate\Support\Collection
    {
        return collect(is_array($condition->condition) ? $condition->condition : (json_decode($condition->condition, true) ?: []))
            ->map(function ($item) use ($productValues) {
                foreach (['includes', 'excludes'] as $filterType) {
                    $item[$filterType] = $this->parseIncludeExclude($item[$filterType]);
                }
                $item['product_values'] = $productValues;

                foreach ($item['excludes'] as $excludeId) {
                    if ($item['product_values'][$excludeId] ?? false) {
                        unset($item['product_values'][$excludeId]);
                    }
                }
                $arrIntersect           = array_intersect($item['includes'], array_keys($item['product_values']));
                $item['product_values'] = collect($item['product_values'])->filter(function ($item, $key) use ($arrIntersect) {
                    return in_array($key, $arrIntersect);
                })->toArray();

                $item['total_qty'] = $item['total_product'] = $item['total_point'] = $item['total_amount'] = 0;
                foreach ($item['includes'] as $_productId) {
                    if ($productValue = $item['product_values'][$_productId] ?? []) {
                        $item['total_qty']    += $productValue['qty'];
                        $item['total_amount'] += $productValue['qty'] * $productValue['price'];
                        $item['total_point']  += $productValue['qty'] * $productValue['point'];
                        $item['total_product']++;
                    }
                }

                return $item;
            });
    }

    /**
     * @param $values
     * @return array
     */
    function parseIncludeExclude($values): array
    {
        $products = [];
        if ($values['all'] ?? false) {
            $products = $this->productRepository->getActiveProduct();
        }
        if ($values['products'] ?? []) {
            $products = $this->productRepository->getByArrId($values['products'])
                ->filter(function ($item) {
                    return $item->status == Product::STATUS_ACTIVE;
                });
        }
        if ($values['subGroups'] ?? []) {
            $currentDate = now()->format('Y-m-d');
            $productIds  = $this->productGroupPriorityRepository->getList(['productGroup'], [
                'minDate' => $currentDate,
                'maxDate' => $currentDate,
            ])
                ->filter(function ($item) use ($values) {
                    return in_array($item->sub_group_id, $values['subGroups']) && $item->product->status == Product::STATUS_ACTIVE;
                })
                ->pluck('product_id')
                ->toArray();

            if ($productIds) {
                $products = $this->productRepository->getByArrId($productIds);
            }
        }
        return $products ? $products->pluck('id')->toArray() : [];
    }

    /**
     * @param $promotionCondition
     * @return array
     */
    function parseDiscountByTotalPoint($promotionCondition): array
    {
        $discounts = [];
        foreach ($promotionCondition as $conditionType) {
            list('total_point' => $totalPoint, 'total_product' => $totalProduct) = $conditionType;
            foreach (collect($conditionType['conditions'])->sortByDesc('minPoint') as $conditionItem) {
                if ($totalProduct >= $conditionItem['minType'] && $totalPoint >= $conditionItem['minPoint']) {
                    $_loop      = floor($totalPoint / $conditionItem['minPoint']);
                    $totalPoint -= $_loop * $conditionItem['minPoint'];

                    foreach ($conditionType['product_values'] as $productId => $productValue) {
                        $_amount = $productValue['qty'] * $productValue['price'];
                        $__loop  = ($conditionItem['minPoint'] == 1 ? 1 : floor(($productValue['point'] * $productValue['qty']) / $conditionItem['minPoint']));

                        $discounts['products'][$productId]['details'][] = $this->getDiscountAmounts($conditionItem, $_amount, $productValue['qty'], $__loop);
                    }
                }
            }
        }

        return $discounts;
    }

    /**
     * @param $promotionCondition
     * @return array
     */
    function parseDiscountByTotalCost($promotionCondition): array
    {
        $discounts = [];
        foreach ($promotionCondition as $conditionType) {
            list('total_product' => $totalProduct, 'total_amount' => $totalAmount) = $conditionType;
            foreach (collect($conditionType['conditions'])->sortByDesc('minCost') as $conditionItem) {
                if ($totalProduct >= $conditionItem['minType'] && $totalAmount >= $conditionItem['minCost']) {
                    $_loop       = floor($totalAmount / $conditionItem['minCost']);
                    $totalAmount -= $_loop * $conditionItem['minCost'];

                    foreach ($conditionType['product_values'] as $productId => $productValue) {
                        $_amount = $productValue['qty'] * $productValue['price'];
                        $__loop  = ($conditionItem['minCost'] == 1 ? 1 : floor($_amount / $conditionItem['minCost']));

                        $discounts['products'][$productId]['details'][] = $this->getDiscountAmounts($conditionItem, $_amount, $productValue['qty'], $__loop);
                    }
                }
            }
        }

        return $discounts;
    }

    /**
     * @param $promotionCondition
     * @return array
     */
    function parseDiscountByTotalQty($promotionCondition): array
    {
        $discounts = [];
        foreach ($promotionCondition as $conditionType) {
            list('total_qty' => $totalQty, 'total_product' => $totalProduct) = $conditionType;
            foreach (collect($conditionType['conditions'])->sortByDesc('minQty') as $conditionItem) {
                if ($totalProduct >= $conditionItem['minType'] && $totalQty >= $conditionItem['minQty']) {
                    $_loop    = floor($totalQty / $conditionItem['minQty']);
                    $totalQty -= $_loop * $conditionItem['minQty'];

                    foreach ($conditionType['product_values'] as $productId => $productValue) {
                        $_amount = $productValue['qty'] * $productValue['price'];
                        $__loop  = ($conditionItem['minQty'] == 1 ? 1 : floor($productValue['qty'] / $conditionItem['minQty']));

                        $discounts['products'][$productId]['details'][] = $this->getDiscountAmounts($conditionItem, $_amount, $productValue['qty'], $__loop);
                    }
                }
            }
        }

        return $discounts;
    }

    /**
     * @param $conditions
     * @return array
     */
    function parseDiscountByQty($conditions): array
    {
        $discounts = [];
        foreach ($conditions as $conditionType) {
            $productValues = $conditionType['product_values'];
            foreach ($productValues as $productId => $productValue) {
                list('qty' => $productQty, 'price' => $productPrice) = $productValue;
                foreach (collect($conditionType['conditions'])->sortByDesc('productQty') as $conditionItem) {
                    if ($productQty >= $conditionItem['productQty']) {
                        $_loop      = floor($productQty / $conditionItem['productQty']);
                        $_qty       = $_loop * $conditionItem['productQty'];
                        $_amount    = $_qty * $productPrice;
                        $productQty -= $_qty;
                        $__loop     = $conditionItem['productQty'] == 1 ? 1 : $_loop;

                        $discounts['products'][$productId]['details'][] = $this->getDiscountAmounts($conditionItem, $_amount, $_qty, $__loop);
                    }
                }
            }
        }

        return $discounts;
    }

    /**
     * @param $promotionCondition
     * @return array
     */
    function parseGiftByTotalPoint($promotionCondition): array
    {
        $gifts = [];
        foreach ($promotionCondition as $condition) {
            list('total_product' => $totalProduct, 'total_point' => $totalPoint) = $condition;
            foreach (collect($condition['conditions'])->sortByDesc('minPoint') as $conditionItem) {
                if ($totalProduct >= $conditionItem['minType'] && $totalPoint >= $conditionItem['minPoint']) {
                    $max        = floor($totalPoint / $conditionItem['minPoint']);
                    $totalPoint -= $max * $conditionItem['minPoint'];

                    if (!isset($gifts[$conditionItem['gift']])) {
                        $gifts[$conditionItem['gift']] = 0;
                    }
                    $gifts[$conditionItem['gift']] += $conditionItem['giftQty'] * $max;
                }
            }
        }

        return $gifts;
    }

    /**
     * @param $promotionCondition
     * @return array
     */
    function parseGiftByTotalCost($promotionCondition): array
    {
        $gifts = [];
        foreach ($promotionCondition as $condition) {
            list('total_product' => $totalProduct, 'total_amount' => $totalAmount) = $condition;
            foreach (collect($condition['conditions'])->sortByDesc('minCost') as $conditionItem) {
                if ($totalProduct >= $conditionItem['minType'] && $totalAmount >= $conditionItem['minCost']) {
                    $max         = floor($totalAmount / $conditionItem['minCost']);
                    $totalAmount -= $max * $conditionItem['minCost'];

                    if (!isset($gifts[$conditionItem['gift']])) {
                        $gifts[$conditionItem['gift']] = 0;
                    }
                    $gifts[$conditionItem['gift']] += $conditionItem['giftQty'] * $max;
                }
            }
        }

        return $gifts;
    }

    /**
     * @param $promotionCondition
     * @return array
     */
    function parseGiftByTotalQty($promotionCondition): array
    {
        $gifts = [];
        foreach ($promotionCondition as $condition) {
            list('total_qty' => $totalQty, 'total_product' => $totalProduct) = $condition;
            foreach (collect($condition['conditions'])->sortByDesc('minQty') as $conditionItem) {
                if ($totalProduct >= $conditionItem['minType'] && $totalQty >= $conditionItem['minQty']) {
                    $max      = floor($totalQty / $conditionItem['minQty']);
                    $totalQty -= $max * $conditionItem['minQty'];

                    if (!isset($gifts[$conditionItem['gift']])) {
                        $gifts[$conditionItem['gift']] = 0;
                    }
                    $gifts[$conditionItem['gift']] += $conditionItem['giftQty'] * $max;
                }
            }
        }

        return $gifts;
    }

    /**
     * @param $promotionCondition
     * @return array
     */
    function parseGiftByQty($promotionCondition): array
    {
        $gifts = [];
        foreach ($promotionCondition as $condition) {
            list('total_qty' => $totalQty) = $condition;
            foreach (collect($condition['conditions'])->sortByDesc('productQty') as $conditionItem) {
                if ($totalQty >= $conditionItem['productQty']) {
                    $max      = floor($totalQty / $conditionItem['productQty']);
                    $totalQty -= $max * $conditionItem['productQty'];

                    if (!isset($gifts[$conditionItem['gift']])) {
                        $gifts[$conditionItem['gift']] = 0;
                    }
                    $gifts[$conditionItem['gift']] += $conditionItem['giftQty'] * $max;
                }
            }
        }

        return $gifts;
    }

    /**
     * @param array $conditionItem
     * @param int $totalAmount
     * @param int $qty
     * @param int $loop
     * @return array
     */
    public function getDiscountAmounts(array $conditionItem, int $totalAmount, int $qty = 0, int $loop = 1): array
    {
        $discountValue = (int)$conditionItem['discount'];
        $limit         = $conditionItem['limitDiscount'] ?? $conditionItem['maxDiscount'] ?? 0;
        $amount        = $conditionItem['type'] == PromotionCondition::TYPE_DISCOUNT_PERCENT ? (($totalAmount / 100) * $discountValue) : $discountValue;

        if ($conditionItem['type'] == PromotionCondition::TYPE_DISCOUNT_PERCENT && $limit && $amount > $limit) {
            $amount = $limit;
        }
        $amount = $loop * $amount;

        return [
            'qty'      => $qty,
            'amount'   => $totalAmount,
            'discount' => $amount,
            'percent'  => round(($amount * 100) / $totalAmount, 2),
        ];
    }
}
