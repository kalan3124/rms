<?php

return [
    'oracle' => [
        'driver'         => 'oracle',
        'tns'            => env('SECOND_DB_TNS', ''),
        'host'           => env('SECOND_DB_HOST', '192.168.2.11'),
        'port'           => env('SECOND_DB_PORT', '1524'),
        'database'       => env('SECOND_DB_DATABASE', ''),
        'username'       => env('SECOND_DB_USERNAME', ''),
        'password'       => env('SECOND_DB_PASSWORD', ''),
        'charset'        => env('SECOND_DB_CHARSET', 'AL32UTF8'),
        'prefix'         => env('SECOND_DB_PREFIX', ''),
        'prefix_schema'  => env('SECOND_DB_SCHEMA_PREFIX', ''),
        'edition'        => env('SECOND_DB_EDITION', 'ora$base'),
        'server_version' => env('SECOND_DB_SERVER_VERSION', '11g'),
    ],
    'oracle_test' => [
        'driver'         => 'oracle',
        'tns'            => env('TEST_ORACLE_DB_TNS', ''),
        'host'           => env('TEST_ORACLE_DB_HOST', '192.168.2.11'),
        'port'           => env('TEST_ORACLE_DB_PORT', '1524'),
        'database'       => env('TEST_ORACLE_DB_DATABASE', ''),
        'username'       => env('TEST_ORACLE_DB_USERNAME', ''),
        'password'       => env('TEST_ORACLE_DB_PASSWORD', ''),
        'charset'        => env('TEST_ORACLE_DB_CHARSET', 'AL32UTF8'),
        'prefix'         => env('TEST_ORACLE_DB_PREFIX', ''),
        'prefix_schema'  => env('TEST_ORACLE_DB_SCHEMA_PREFIX', ''),
        'edition'        => env('TEST_ORACLE_DB_EDITION', 'ora$base'),
        'server_version' => env('TEST_ORACLE_DB_SERVER_VERSION', '11g'),
    ],
];
