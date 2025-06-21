<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WooCommerceService;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
         // Register WooCommerceService as a singleton
      $this->app->singleton(WooCommerceService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
