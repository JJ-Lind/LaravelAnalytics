<?php

namespace WezanEnterprises\LaravelAnalytics\src\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Analytics
 *
 * This is the facade class for interacting with the Laravel Analytics package.
 * It provides a simplified and expressive API for accessing analytics functionality.
 *
 * @package WezanEnterprises\LaravelAnalytics\src\Facades
 */
class Analytics extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-analytics';
    }
}