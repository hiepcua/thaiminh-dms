<?php

namespace App\Repositories\PromotionCondition;

use App\Models\PromotionCondition;
use App\Repositories\BaseRepository;

class PromotionConditionRepository extends BaseRepository implements PromotionConditionRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new PromotionCondition();
    }

    public function getConditionsOfPromotion($promotion)
    {
        return $this->model->where('promotion_id', $promotion->id)
            ->get();
    }

    public function deleteConditionOfPromotion($promotionId)
    {
        $this->model->where('promotion_id', $promotionId)->delete();
    }
}
