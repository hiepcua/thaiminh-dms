<?php

namespace App\Repositories\Checkin;

use App\Repositories\BaseRepositoryInterface;

interface CheckinRepositoryInterface extends BaseRepositoryInterface
{
    public function getStoresChecked($tdvId, $from, $to);

    public function getToDayCheckin($storeId, $tdvId);

    public function getToDayChecked($storeId, $tdvId);

    public function getCheckinInfoOfIds($userIds, $from, $to);
}
