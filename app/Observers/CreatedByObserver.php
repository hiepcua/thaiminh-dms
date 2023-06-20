<?php

namespace App\Observers;

use App\Helpers\Helper;

class CreatedByObserver
{
    /**
     * Handle the Customer "created" event.
     *
     * @param $model
     * @return void
     */
    public function creating($model)
    {
        if ($model->created_by === null) {
            $current_user      = Helper::currentUser();
            $model->created_by = $current_user?->id ?? 0;
        }
    }

    public function updating($model)
    {
        if ($model->updated_by === null) {
            $current_user      = Helper::currentUser();
            $model->updated_by = $current_user?->id ?? 0;
        }
    }
}
