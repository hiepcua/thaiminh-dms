<?php

namespace App\Observers;

use App\Models\Organization;

class OrganizationObserver
{
    /**
     * Handle the Organization "created" event.
     *
     * @param Organization $organization
     * @return void
     */
    public function created(Organization $organization)
    {
        cache()->forget(Organization::CACHE_KEY_ALL_ACTIVE);
    }

    /**
     * Handle the Organization "updated" event.
     *
     * @param Organization $organization
     * @return void
     */
    public function updated(Organization $organization)
    {
        cache()->forget(Organization::CACHE_KEY_ALL_ACTIVE);
    }

    /**
     * Handle the Organization "deleted" event.
     *
     * @param Organization $organization
     * @return void
     */
    public function deleted(Organization $organization)
    {
        cache()->forget(Organization::CACHE_KEY_ALL_ACTIVE);
    }

    /**
     * Handle the Organization "restored" event.
     *
     * @param Organization $organization
     * @return void
     */
    public function restored(Organization $organization)
    {
        //
    }

    /**
     * Handle the Organization "force deleted" event.
     *
     * @param Organization $organization
     * @return void
     */
    public function forceDeleted(Organization $organization)
    {
        //
    }
}
