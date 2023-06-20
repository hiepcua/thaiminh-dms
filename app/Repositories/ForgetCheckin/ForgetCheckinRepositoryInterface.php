<?php

namespace App\Repositories\ForgetCheckin;

use App\Repositories\BaseRepositoryInterface;

interface ForgetCheckinRepositoryInterface extends BaseRepositoryInterface
{
    public function getByCreator($creatorId = null, $from = null, $to = null, $status = null);
}
