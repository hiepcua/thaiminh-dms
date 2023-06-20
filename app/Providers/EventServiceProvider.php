<?php

namespace App\Providers;

use App\Listeners\LastLoggedIn;
use App\Models\Agency;
use App\Models\AgencyOrder;
use App\Models\File;
use App\Models\Gift;
use App\Models\Organization;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductGroupPriority;
use App\Models\Promotion;
use App\Models\Rank;
use App\Models\RevenuePeriod;
use App\Models\Store;
use App\Models\StoreChange;
use App\Models\StoreCoordinateChange;
use App\Models\StoreOrder;
use App\Observers\AgencyOrderObserver;
use App\Observers\CreatedByObserver;
use App\Observers\OrganizationObserver;
use App\Observers\StoreObserver;
use App\Observers\StoreOrderObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Login::class => [
            LastLoggedIn::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Organization::observe(CreatedByObserver::class);
        Product::observe(CreatedByObserver::class);
        ProductGroup::observe(CreatedByObserver::class);
        ProductGroupPriority::observe(CreatedByObserver::class);
        Gift::observe(CreatedByObserver::class);
        File::observe(CreatedByObserver::class);
        Agency::observe(CreatedByObserver::class);
        AgencyOrder::observe(CreatedByObserver::class);
        Promotion::observe(CreatedByObserver::class);
        Rank::observe(CreatedByObserver::class);
        RevenuePeriod::observe(CreatedByObserver::class);
        Store::observe(CreatedByObserver::class);
        StoreChange::observe(CreatedByObserver::class);
        StoreOrder::observe(CreatedByObserver::class);
        //
        Store::observe(StoreObserver::class);
        StoreOrder::observe(StoreOrderObserver::class);
        AgencyOrder::observe(AgencyOrderObserver::class);
        Organization::observe(OrganizationObserver::class);
    }
}
