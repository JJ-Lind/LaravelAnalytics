<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Service Account Credentials JSON
    |--------------------------------------------------------------------------
    |
    | This is the path to the client secret JSON file. You can obtain this file
    | as described in the package's README. Alternatively, you can pass the
    | credentials as an array instead of a file path.
    |
    */
    'service_account_credentials_json' => storage_path('app/analytics/service-account-credentials.json'),

    /*
    |--------------------------------------------------------------------------
    | Cache Lifetime in Minutes
    |--------------------------------------------------------------------------
    |
    | The amount of time, in minutes, that Google API responses will be cached.
    | If set to zero, responses won't be cached at all.
    |
    */
    'cache_lifetime_in_minutes' => 60 * 24,

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Here, you can configure the "store" used by the underlying Google_Client
    | to store its data. You can also add extra parameters to be passed to
    | setCacheConfig (refer to the google-api-php-client documentation).
    |
    | Optional parameters: "lifetime", "prefix"
    |
    */
    'cache' => [
        'store' => 'file'
    ]
];