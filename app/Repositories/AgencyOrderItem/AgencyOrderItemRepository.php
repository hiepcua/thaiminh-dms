<?php

namespace App\Repositories\AgencyOrderItem;

use App\Models\AgencyOrderItem;
use App\Repositories\BaseRepository;

class AgencyOrderItemRepository extends BaseRepository implements AgencyOrderItemRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new AgencyOrderItem();
    }
}
