# Configuration

The configuration file is published to `config/four-corners.php`.

## Identifier Type

Configure whether to use auto-incrementing IDs or UUID7:

```php
'id_type' => env('FOUR_CORNERS_ID_TYPE', 'incrementing'),
```

Options:
- `'incrementing'` - Standard auto-incrementing integer IDs
- `'uuid7'` - Time-ordered UUIDs (recommended for distributed systems)

## Table Prefix

Add a prefix to all database tables:

```php
'table_prefix' => env('FOUR_CORNERS_TABLE_PREFIX', ''),
```

Example: Setting `'fc_'` creates tables like `fc_document_types`.

## Multi-Tenant Support

Enable tenant isolation:

```php
'tenant' => [
    'enabled' => env('FOUR_CORNERS_TENANT_ENABLED', false),
    'column' => env('FOUR_CORNERS_TENANT_COLUMN', 'tenant_id'),
],
```

When enabled, all queries are automatically scoped by tenant.

## Routes

Configure API route settings:

```php
'routes' => [
    'enabled' => true,
    'prefix' => 'api/four-corners',
    'middleware' => ['api', 'auth:sanctum'],
],
```

Disable `enabled` if you want to define routes manually.

## Document Types

Define document types in config (alternative to database):

```php
'document_types' => [
    [
        'code' => 'us_drivers_license',
        'name' => 'US Driver\'s License',
        'aspect_ratio' => [856, 540],
        'display_size' => [428, 270],
        'archive_size' => [856, 540],
    ],
    [
        'code' => 'us_passport',
        'name' => 'US Passport',
        'aspect_ratio' => [1050, 750],
        'display_size' => [525, 375],
        'archive_size' => [1050, 750],
    ],
],
```

## Output Settings

Configure image output:

```php
'output' => [
    'format' => 'jpeg',        // 'jpeg' or 'png'
    'quality' => 0.9,          // 0.0 to 1.0 for JPEG
    'display_max_dimension' => 800,
    'archive_max_dimension' => 2000,
],
```

## Queue Configuration

Configure background processing:

```php
'queue' => [
    'connection' => env('FOUR_CORNERS_QUEUE_CONNECTION', 'default'),
    'queue' => env('FOUR_CORNERS_QUEUE', 'annotations'),
],
```

## Training Data Export

Configure ML training data export:

```php
'training_export' => [
    'path' => storage_path('app/training-data'),
    'format' => 'jsonl',
    'include_images' => false,
    'anonymize' => true,
],
```

## OpenCV.js URL

Configure OpenCV.js CDN URL:

```php
'opencv_url' => env(
    'FOUR_CORNERS_OPENCV_URL',
    'https://docs.opencv.org/4.9.0/opencv.js'
),
```

You can host OpenCV.js locally for better performance:

```php
'opencv_url' => asset('js/opencv.js'),
```

## Full Configuration Example

```php
<?php

return [
    'id_type' => 'uuid7',
    'table_prefix' => '',

    'tenant' => [
        'enabled' => true,
        'column' => 'organization_id',
    ],

    'routes' => [
        'enabled' => true,
        'prefix' => 'api/annotations',
        'middleware' => ['api', 'auth:sanctum'],
    ],

    'output' => [
        'format' => 'jpeg',
        'quality' => 0.9,
    ],

    'queue' => [
        'connection' => 'redis',
        'queue' => 'document-processing',
    ],

    'opencv_url' => 'https://docs.opencv.org/4.9.0/opencv.js',
];
```

## Environment Variables

All settings can be configured via environment variables:

```env
FOUR_CORNERS_ID_TYPE=uuid7
FOUR_CORNERS_TABLE_PREFIX=fc_
FOUR_CORNERS_TENANT_ENABLED=true
FOUR_CORNERS_TENANT_COLUMN=tenant_id
FOUR_CORNERS_QUEUE_CONNECTION=redis
FOUR_CORNERS_QUEUE=annotations
FOUR_CORNERS_OPENCV_URL=https://docs.opencv.org/4.9.0/opencv.js
```
