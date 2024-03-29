<?php

return [

    'default' => env('ORBIT_DEFAULT_DRIVER', 'json'),

    'drivers' => [
        'md' => \Orbit\Drivers\Markdown::class,
        'json' => \Orbit\Drivers\Json::class,
        'yaml' => \Orbit\Drivers\Yaml::class,
    ],

    'paths' => [
        'content' => storage_path('content'),
        'cache' => storage_path('content/orbit'),
    ],

];
