<?php

namespace App\Repositories\AgencyOrderTDV;

use App\Models\AgencyOrderTDV;
use App\Repositories\BaseRepository;

class AgencyOrderTDVRepository extends BaseRepository implements AgencyOrderTDVRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new AgencyOrderTDV();
    }
}
