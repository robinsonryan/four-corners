<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | FourCorners Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your four-corners package settings here.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Identifier Type
    |--------------------------------------------------------------------------
    |
    | Configure whether to use auto-incrementing IDs or UUID7.
    | Options: 'incrementing', 'uuid7'
    |
    */
    'id_type' => env('FOUR_CORNERS_ID_TYPE', 'uuid7'),

    /*
    |--------------------------------------------------------------------------
    | Table Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix for all database tables created by this package.
    | Leave empty for no prefix.
    |
    */
    'table_prefix' => env('FOUR_CORNERS_TABLE_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenant Configuration
    |--------------------------------------------------------------------------
    |
    | Configure multi-tenant support for the package.
    |
    */
    'tenant' => [
        'enabled' => env('FOUR_CORNERS_TENANT_ENABLED', false),
        'column' => env('FOUR_CORNERS_TENANT_COLUMN', 'tenant_id'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'prefix' => 'admin/annotations',
        'middleware' => ['web', 'auth'],
        'name_prefix' => 'four-corners.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Types
    |--------------------------------------------------------------------------
    | Default document types and their aspect ratios.
    | Additional types can be added via the seeder or directly to the database.
    */
    'document_types' => [
        'us_drivers_license' => [
            'name' => "US Driver's License",
            'aspect_ratio' => [3.375, 2.125],
            'display_size' => [850, 536],
            'archive_size' => [1700, 1072],
        ],
        'us_passport' => [
            'name' => 'US Passport',
            'aspect_ratio' => [5.0, 3.5],
            'display_size' => [850, 595],
            'archive_size' => [1700, 1190],
        ],
        'us_passport_card' => [
            'name' => 'US Passport Card',
            'aspect_ratio' => [3.375, 2.125],
            'display_size' => [850, 536],
            'archive_size' => [1700, 1072],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Output Configuration
    |--------------------------------------------------------------------------
    */
    'output' => [
        'jpeg_quality' => 90,
        'format' => 'jpeg',
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'connection' => env('FOUR_CORNERS_QUEUE_CONNECTION', 'database'),
        'queue_name' => env('FOUR_CORNERS_QUEUE_NAME', 'annotations'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Training Data Export
    |--------------------------------------------------------------------------
    */
    'training_export' => [
        'format' => 'jsonl',
        'include_rejected' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenCV.js CDN URL
    |--------------------------------------------------------------------------
    | The URL to load OpenCV.js from. Can be changed to a local copy.
    */
    'opencv_url' => env(
        'FOUR_CORNERS_OPENCV_URL',
        'https://docs.opencv.org/4.9.0/opencv.js',
    ),
];
