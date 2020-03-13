<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | Here you can change the setting for mail web
    | MAILWEB_LIMIT - limit the amount of messages stored in the db
    |
    */
    'MAILWEB_ENABLED' => env('MAILWEB_ENABLED', true),
    'MAILWEB_LIMIT' => env('MAILWEB_LIMIT', 20),
    'MAILWEB_TOOLBAR' => [
        'LARGE_SCREEN' => env('MAILWEB_LARGE_SCREEN', true),
        'MEDIUM_SCREEN' => env('MAILWEB_MEDIUM_SCREEN', true),
        'SMALL_SCREEN' => env('MAILWEB_SMALL_SCREEN', true),
        'HTML' => env('MAILWEB_HTML', true),
        'MARKDOWN' => env('MAILWEB_MARKDOWN', true),
    ],
];
