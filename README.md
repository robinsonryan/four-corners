# Four Corners

A Laravel package for document annotation with bounding box selection, perspective transformation, and ML training data collection.

## Features

- **Client-side Processing**: All image transformation happens in the browser using OpenCV.js
- **Corner Detection**: Auto-detect document corners using OpenCV.js contour detection
- **Perspective Transform**: Apply perspective correction to straighten document images
- **Manual Adjustment**: Draggable corner handles for precise control
- **Rotation Control**: Support for 0°, 90°, 180°, 270° rotation
- **Rejection Workflow**: Categorized rejection reasons for unusable images
- **Training Data**: Collect structured data for future ML model development
- **Queue-based Processing**: Background processing via Laravel queues
- **Event-driven**: Hook into annotation lifecycle events

## Quick Start

Get the annotation workflow running in under 2 minutes:

```bash
# Install
composer require robinsonryan/four-corners

# Setup database
php artisan vendor:publish --tag=four-corners-migrations
php artisan migrate
php artisan db:seed --class="RobinsonRyan\\FourCorners\\Database\\Seeders\\FourCornersSeeder"

# Try the demo
open http://localhost/admin/annotations/demo
```

The demo page lets you test corner detection, adjustment, and image transformation with any image.

## Installation

```bash
composer require robinsonryan/four-corners
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=four-corners-config
```

Publish migrations:

```bash
php artisan vendor:publish --tag=four-corners-migrations
php artisan migrate
```

Publish Vue components:

```bash
php artisan vendor:publish --tag=four-corners-components
```

## Usage

### Starting an Annotation

```php
use RobinsonRyan\FourCorners\Facades\FourCorners;

$annotation = FourCorners::start(
    originalImagePath: 's3://bucket/images/document.jpg',
    originalWidth: 1200,
    originalHeight: 800,
    documentTypeId: $documentType->id,
);
```

### Completing an Annotation

```php
$annotation = FourCorners::complete(
    annotationId: $annotation->id,
    finalCorners: $cornersData,
    finalRotation: 0,
    annotatedBy: auth()->id(),
    timeSpentSeconds: 15.5,
    displayImageBase64: $displayImage,
    archiveImageBase64: $archiveImage,
);
```

### Rejecting an Annotation

```php
$annotation = FourCorners::reject(
    annotationId: $annotation->id,
    rejectionReasonId: $reason->id,
    notes: 'Image is too blurry',
    rejectedBy: auth()->id(),
    timeSpentSeconds: 5.0,
);
```

### Listening to Events

```php
// In EventServiceProvider
use RobinsonRyan\FourCorners\Events\AnnotationCompleted;

protected $listen = [
    AnnotationCompleted::class => [
        StoreAnnotationImages::class,
    ],
];
```

### Exporting Training Data

```bash
php artisan four-corners:export-training \
  --from="2025-01-01" \
  --to="2025-06-30" \
  --document-type=us_drivers_license \
  --include-rejected \
  --output=training-data.jsonl
```

## Vue Components

After publishing, import the annotator component:

```vue
<script setup lang="ts">
import { DocumentAnnotator } from '@/vendor/four-corners';
</script>

<template>
  <DocumentAnnotator
    :image-url="imageUrl"
    :document-type="documentType"
    :rejection-reasons="rejectionReasons"
    @complete="handleComplete"
    @reject="handleReject"
  />
</template>
```

## Testing

```bash
composer test
```

## Development

### Code Quality

```bash
# Run all quality checks
composer quality

# Individual commands
composer lint        # Fix code style
composer lint:check  # Check code style
composer analyze     # Run static analysis
composer test        # Run tests
```

### DDEV

```bash
ddev start    # Start development environment
ddev test     # Run tests
ddev quality  # Run all quality checks
```

## License

MIT License. See [LICENSE.md](LICENSE.md) for details.
