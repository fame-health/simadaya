<?php

return [
    'default' => env('BROADCAST_CONNECTION', 'log'),

    'connections' => [
        'reverb' => [
            'driver' => 'pusher',
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),
            'options' => [
                'host' => env('REVERB_HOST', '127.0.0.1'),
                'port' => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),
                'encrypted' => env('REVERB_SCHEME', 'http') === 'https',
                'useTLS' => env('REVERB_SCHEME', 'http') === 'https',
            ],
            'client_options' => [],
        ],

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY', env('REVERB_APP_KEY')),
            'secret' => env('PUSHER_APP_SECRET', env('REVERB_APP_SECRET')),
            'app_id' => env('PUSHER_APP_ID', env('REVERB_APP_ID')),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                'host' => env('PUSHER_HOST', env('REVERB_HOST', '127.0.0.1')),
                'port' => env('PUSHER_PORT', env('REVERB_PORT', 8080)),
                'scheme' => env('PUSHER_SCHEME', env('REVERB_SCHEME', 'http')),
                'encrypted' => env('PUSHER_SCHEME', env('REVERB_SCHEME', 'http')) === 'https',
                'useTLS' => env('PUSHER_SCHEME', env('REVERB_SCHEME', 'http')) === 'https',
            ],
            'client_options' => [],
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],
    ],
];
