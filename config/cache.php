<?php

return [
    'default' => env('CACHE_STORE', 'array'),
    'stores' => [
        'array' => ['driver' => 'array'],
        'redis' => ['driver' => 'redis', 'connection' => 'cache'],
    ],
    'prefix' => env('CACHE_PREFIX', 'auth_cache'),
];
