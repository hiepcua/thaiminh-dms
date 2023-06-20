<?php

namespace App\Repositories\PromotionCondition;

use App\Repositories\BaseRepositoryInterface;

interface PromotionConditionRepositoryInterface extends BaseRepositoryInterface
{
    public function getConditionsOfPromotion($promotion);

    public function deleteConditionOfPromotion($promotionId);
}
