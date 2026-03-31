# Installation

## Requirements

- PHP 8.3+
- Laravel 11.x or 12.x
- Node.js 18+ (for frontend assets)
- Vue 3.5+

## Install via Composer

```bash
composer require robinsonryan/four-corners
```

## Publish Configuration

```bash
php artisan vendor:publish --tag=four-corners-config
```

This publishes `config/four-corners.php` where you can customize:
- Document types
- Rejection reasons
- Output settings
- Queue configuration
- Training data export options

## Run Migrations

```bash
php artisan vendor:publish --tag=four-corners-migrations
php artisan migrate
```

This creates three tables:
- `document_types` - Document type definitions
- `rejection_reasons` - Rejection reason categories
- `document_annotations` - Annotation records

## Publish Vue Components

```bash
php artisan vendor:publish --tag=four-corners-components
```

This publishes Vue components to `resources/js/vendor/four-corners/`.

## Install Frontend Dependencies

Add the required npm packages to your project:

```bash
npm install konva vue-konva
```

OpenCV.js is loaded dynamically from CDN by default, but you can configure a custom URL in the config.

## Register Event Listeners

In your `EventServiceProvider`, register listeners for annotation events:

```php
use RobinsonRyan\FourCorners\Events\AnnotationCompleted;
use RobinsonRyan\FourCorners\Events\AnnotationRejected;

protected $listen = [
    AnnotationCompleted::class => [
        \App\Listeners\StoreAnnotationImages::class,
    ],
    AnnotationRejected::class => [
        \App\Listeners\HandleRejectedAnnotation::class,
    ],
];
```

## Configure Queue (Optional)

For background processing, ensure your queue is configured:

```bash
php artisan queue:work
```

The package dispatches `ProcessAnnotationJob` to the queue specified in `config/four-corners.php`.

## Seed Default Data (Optional)

Create default document types and rejection reasons:

```php
use RobinsonRyan\FourCorners\Models\DocumentType;
use RobinsonRyan\FourCorners\Models\RejectionReason;

// Document types
DocumentType::create([
    'code' => 'us_drivers_license',
    'name' => 'US Driver\'s License',
    'aspect_ratio_width' => 856,
    'aspect_ratio_height' => 540,
    'display_width' => 428,
    'display_height' => 270,
    'archive_width' => 856,
    'archive_height' => 540,
    'is_active' => true,
]);

// Rejection reasons
RejectionReason::create([
    'code' => 'blurry',
    'label' => 'Image is blurry',
    'description' => 'The image is too blurry to read document details',
    'sort_order' => 1,
    'is_active' => true,
]);
```

## Verify Installation

Run the package tests to verify everything is working:

```bash
php artisan test --filter=FourCorners
```

## Next Steps

- [Configuration](configuration.md) - Customize package settings
- [Usage](usage.md) - Learn how to use the annotation workflow
