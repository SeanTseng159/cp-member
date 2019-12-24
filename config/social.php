<?php

return [
    'facebook' => [
        'app_id' => env('FACEBOOK_APP_ID', ''),
        'app_secert' => env('FACEBOOK_APP_SECRET', ''),
    ],
    'google' => [
        'web_client_id' =>  env('GOOGLE_CLIENT_ID', ''),
    ],
    'line' => [
        'channel_id' => env('LINE_CHANNEL_ID', ''),
        'channel_secret' => env('LINE_CHANNEL_SECRET', ''),
    ],
];
