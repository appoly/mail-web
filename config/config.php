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
    'MAILWEB_LIMIT' => env('MAILWEB_LIMIT', 30),
    'MAILWEB_SEND_SAMPLE_BUTTON' => env('MAILWEB_SEND_SAMPLE_BUTTON', true),
    'MAILWEB_DELETE_ALL_ENABLED' => env('MAILWEB_DELETE_ALL_ENABLED', false),
    'MAILWEB_RETURN' => [
        'APP_NAME' => env('MAILWEB_RETURN_APP_NAME'),
        'APP_URL' => env('MAILWEB_RETURN_APP_URL'),
    ],
    'MAILWEB_ATTACHMENTS' => [
        'DISK' => env('MAILWEB_ATTACHMENTS_DISK'),
        'PATH' => env('MAILWEB_ATTACHMENTS_PATH', 'mailweb/attachments'),
    ],
];
