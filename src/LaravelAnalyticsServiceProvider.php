<?php

namespace WezanEnterprises\LaravelAnalytics\src;

use Illuminate\Support\ServiceProvider;
use WezanEnterprises\LaravelAnalytics\src\Utility\{Formatter, OrderBy, Validator};

/**
 * Class LaravelAnalyticsServiceProvider
 *
 * This service provider registers the `Formatter` class as a singleton in the Laravel service container.
 *
 * @package WezanEnterprises\LaravelAnalytics
 */
class LaravelAnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {}

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('formatter', fn() => [new Formatter]);
        $this->app->singleton('order-by', fn() => [new OrderBy]);
        $this->app->singleton('validator', fn() => [new Validator]);
    }
}