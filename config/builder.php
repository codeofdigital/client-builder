<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Client Builder Caching
    |--------------------------------------------------------------------------
    |
    | Config for caching mechanism in Client Builder
    |
    */
    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Client Builder Enable Cache
        |--------------------------------------------------------------------------
        |
        | To be enabled or disabled caching for Client Builder
        |
        */
        'enabled' => true,

        /*
        |--------------------------------------------------------------------------
        | Client Builder Cache TTL
        |--------------------------------------------------------------------------
        |
        | TTL (Time-To-Live) for data to be cached
        |
        */
        'cache_ttl' => env('CLIENT_CACHE_TTL', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Client Builder Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Config for caching mechanism in Client Builder
    |
    */
    'rate_limit' => [
        /*
        |--------------------------------------------------------------------------
        | Client Builder Enable Rate Limiting
        |--------------------------------------------------------------------------
        |
        | To be enabled or disabled rate limit for Client Builder
        |
        */
        'enabled' => true,

        /*
        |--------------------------------------------------------------------------
        | Client Builder Rate Limiting Threshold
        |--------------------------------------------------------------------------
        |
        | The max threshold for rate limiting
        |
        */
        'threshold' => env('CLIENT_RATE_LIMIT_THRESHOLD', 30),
    ],
];