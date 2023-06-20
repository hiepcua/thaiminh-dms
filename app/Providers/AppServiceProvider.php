<?php

namespace App\Providers;

use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Breadcrumbs::for('home', function ($trail) {
            $trail->push('Tổng quan', route('admin.dashboard.index'));
        });

        Paginator::useBootstrap();

        if (in_array(config('app.env', 'local'), ['production', 'staging'])) {
            URL::forceScheme('https');
        }
    }
}
