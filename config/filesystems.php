<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'throw'  => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw'      => false,
        ],

        's3' => [
            'driver'                  => 's3',
            'key'                     => env('DO_ACCESS_KEY_ID'),
            'secret'                  => env('DO_SECRET_ACCESS_KEY'),
            'region'                  => env('DO_DEFAULT_REGION', 'nyc3'),
            'bucket'                  => env('DO_BUCKET'),
            'url'                     => env('DO_URL'),
            'endpoint'                => env('DO_ENDPOINT'),
            'use_path_style_endpoint' => env('DO_USE_PATH_STYLE_ENDPOINT', false),
            'throw'                   => false,
            'visibility'              => 'public',
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
