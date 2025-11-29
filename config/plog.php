<?php

return [
    'enabled' => env('PLOG_ENABLED', true),

    'database' => [
        'connection' => env('PLOG_DB_CONNECTION', 'plog'),
        'table' => 'plog_entries',
    ],

    'authorized_emails' => env('PLOG_AUTHORIZED_EMAILS')
        ? explode(',', env('PLOG_AUTHORIZED_EMAILS'))
        : [],

    'auto_add_middleware' => true,

    'capture' => [
        'user_id' => true,
        'session_id' => true,
        'request_id' => true,
        'environment' => true,
        'endpoint' => true,
        'file_info' => true,
        'class_info' => true,
    ],

    'retention' => [
        'default_days' => env('PLOG_RETENTION_DAYS', 7),
        'rules' => [
        ],
    ],

    'cleanup' => [
        'enabled' => env('PLOG_CLEANUP_ENABLED', true),
        'schedule' => 'daily',
    ],

    'ui' => [
        'per_page' => 50,
        'realtime' => false,
    ],
];