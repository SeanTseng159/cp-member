<?php

return [

    'default' => env('SMS_PROVIDER', 'easygo'),

    'api_timeout' => 30, // seconds

    'providers' => [
        'easygo' => [
            'url' => 'https://www.easysms.com.tw/easygohttpapi/httpapiservice.asmx',
            'username' => env('SMS_USERNAME'),
            'password' => env('SMS_PASSWORD')
        ],
    ],
    'status' => env('SMS_STATUS'),
];
