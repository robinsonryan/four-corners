# Usage

## Quick Start Demo

After installing the package, you can test the annotation workflow immediately:

```bash
# 1. Publish migrations
php artisan vendor:publish --tag=four-corners-migrations

# 2. Run migrations
php artisan migrate

# 3. Seed default document types and rejection reasons
php artisan db:seed --class="RobinsonRyan\\FourCorners\\Database\\Seeders\\FourCornersSeeder"

# 4. Visit the demo page
open http://localhost/admin/annotations/demo
```

The demo page allows you to:
- Load images from URL or local file
- Test corner detection and adjustment
- Preview perspective transformation
- See complete/reject payloads in the console

You can also pass an image URL directly:

```
http://localhost/admin/annotations/demo?image=https://example.com/document.jpg
```

## Overview

The Four Corners workflow consists of:

1. **Start** - Create an annotation record for an image
2. **Annotate** - User adjusts corners using Vue component
3. **Complete/Reject** - Save results or reject the image
4. **Process** - Background job handles post-processing
5. **Store** - Your app stores the transformed images via events

## Backend Usage

### Starting an Annotation

```php
use RobinsonRyan\FourCorners\Services\AnnotationService;
use RobinsonRyan\FourCorners\Data\AutoDetectionData;

$service = app(AnnotationService::class);

// Basic usage
$annotation = $service->start(
    originalImagePath: 'uploads/document.jpg',
    originalWidth: 1200,
    originalHeight: 800,
    documentTypeId: $documentType->id,
);

// With auto-detection data (from client-side OpenCV.js)
$annotation = $service->start(
    originalImagePath: 'uploads/document.jpg',
    originalWidth: 1200,
    originalHeight: 800,
    documentTypeId: $documentType->id,
    autoDetection: AutoDetectionData::from([
        'method' => 'opencv_js_contour_v1',
        'corners' => [
            'top_left' => ['x' => 50, 'y' => 45],
            'top_right' => ['x' => 1150, 'y' => 50],
            'bottom_right' => ['x' => 1145, 'y' => 755],
            'bottom_left' => ['x' => 55, 'y' => 750],
        ],
        'rotation_suggestion' => 0,
        'confidence' => 0.87,
        'detection_time_ms' => 145,
    ]),
);
```

### Completing an Annotation

```php
use RobinsonRyan\FourCorners\Data\CornersData;

$annotation = $service->complete(
    annotationId: $annotation->id,
    finalCorners: CornersData::from([
        'top_left' => ['x' => 52, 'y' => 48],
        'top_right' => ['x' => 1148, 'y' => 52],
        'bottom_right' => ['x' => 1142, 'y' => 752],
        'bottom_left' => ['x' => 58, 'y' => 748],
    ]),
    finalRotation: 0,
    annotatedBy: auth()->id(),
    timeSpentSeconds: 12.5,
    displayImageBase64: $request->display_image,
    archiveImageBase64: $request->archive_image,
);
```

### Rejecting an Annotation

```php
$annotation = $service->reject(
    annotationId: $annotation->id,
    rejectionReasonId: $reason->id,
    notes: 'Document is partially obscured',
    rejectedBy: auth()->id(),
    timeSpentSeconds: 3.2,
);
```

### Querying Annotations

```php
use RobinsonRyan\FourCorners\Models\DocumentAnnotation;

// Get pending annotations
$pending = DocumentAnnotation::pending()->get();

// Get by document type
$licenses = DocumentAnnotation::forDocumentType($documentType->id)->get();

// Get processed annotations
$processed = DocumentAnnotation::processed()->get();

// Get rejected annotations
$rejected = DocumentAnnotation::rejected()->get();
```

## Frontend Usage

### Basic Component Usage

```vue
<script setup lang="ts">
import { DocumentAnnotator } from '@/vendor/four-corners';
import type { CompletePayload, RejectPayload } from '@/vendor/four-corners';

const props = defineProps<{
  imageUrl: string;
  documentType: object;
  rejectionReasons: object[];
}>();

const handleComplete = async (payload: CompletePayload) => {
  await fetch('/api/annotations/complete', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      annotation_id: payload.annotationId,
      final_corners: payload.finalCorners,
      final_rotation: payload.finalRotation,
      time_spent_seconds: payload.timeSpentSeconds,
      display_image: payload.displayImageBase64,
      archive_image: payload.archiveImageBase64,
    }),
  });
};

const handleReject = async (payload: RejectPayload) => {
  await fetch('/api/annotations/reject', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      annotation_id: payload.annotationId,
      rejection_reason_id: payload.rejectionReasonId,
      notes: payload.notes,
      time_spent_seconds: payload.timeSpentSeconds,
    }),
  });
};
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

### With Suggested Corners

Pass auto-detected corners from a previous session:

```vue
<DocumentAnnotator
  :image-url="imageUrl"
  :document-type="documentType"
  :rejection-reasons="rejectionReasons"
  :suggested-corners="annotation.autoDetection?.corners"
  :suggested-rotation="annotation.autoDetection?.rotationSuggestion"
  :annotation-id="annotation.id"
  @complete="handleComplete"
  @reject="handleReject"
/>
```

### Props Reference

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `imageUrl` | `string` | Yes | URL of the image to annotate |
| `documentType` | `DocumentType` | Yes | Document type configuration |
| `rejectionReasons` | `RejectionReason[]` | Yes | Available rejection reasons |
| `annotationId` | `string` | No | Existing annotation ID |
| `suggestedCorners` | `Corners` | No | Pre-detected corner positions |
| `suggestedRotation` | `Rotation` | No | Suggested rotation (0, 90, 180, 270) |
| `opencvUrl` | `string` | No | Custom OpenCV.js CDN URL |

### Events Reference

| Event | Payload | Description |
|-------|---------|-------------|
| `complete` | `CompletePayload` | User accepted the annotation |
| `reject` | `RejectPayload` | User rejected the image |
| `cancel` | None | User cancelled the annotation |

## Handling Events

### AnnotationCompleted Event

```php
namespace App\Listeners;

use RobinsonRyan\FourCorners\Events\AnnotationCompleted;
use Illuminate\Support\Facades\Storage;

class StoreAnnotationImages
{
    public function handle(AnnotationCompleted $event): void
    {
        $annotation = $event->annotation;

        // Decode and store display image
        $displayData = base64_decode(
            str_replace('data:image/jpeg;base64,', '', $event->displayImageBase64)
        );
        $displayPath = "annotations/{$annotation->id}/display.jpg";
        Storage::put($displayPath, $displayData);

        // Decode and store archive image
        $archiveData = base64_decode(
            str_replace('data:image/jpeg;base64,', '', $event->archiveImageBase64)
        );
        $archivePath = "annotations/{$annotation->id}/archive.jpg";
        Storage::put($archivePath, $archiveData);

        // Update annotation with paths
        $annotation->update([
            'display_image_path' => $displayPath,
            'archive_image_path' => $archivePath,
        ]);
    }
}
```

### AnnotationRejected Event

```php
namespace App\Listeners;

use RobinsonRyan\FourCorners\Events\AnnotationRejected;

class HandleRejectedAnnotation
{
    public function handle(AnnotationRejected $event): void
    {
        $annotation = $event->annotation;

        // Log rejection for review
        logger()->info('Annotation rejected', [
            'annotation_id' => $annotation->id,
            'reason' => $annotation->rejectionReason->label,
            'notes' => $annotation->rejection_notes,
        ]);

        // Optionally queue for re-capture
        // dispatch(new RequestNewCapture($annotation->original_image_path));
    }
}
```

## Exporting Training Data

Export annotation data for ML training:

```bash
# Export all processed annotations
php artisan four-corners:export-training

# Export with filters
php artisan four-corners:export-training \
  --from="2025-01-01" \
  --to="2025-06-30" \
  --document-type=us_drivers_license \
  --output=training-data.jsonl

# Include rejected annotations for negative examples
php artisan four-corners:export-training --include-rejected
```

### JSONL Output Format

```json
{"original_path":"uploads/doc1.jpg","width":1200,"height":800,"corners":{"top_left":{"x":50,"y":45},"top_right":{"x":1150,"y":50},"bottom_right":{"x":1145,"y":755},"bottom_left":{"x":55,"y":750}},"rotation":0,"document_type":"us_drivers_license","auto_detection":{"method":"opencv_js_contour_v1","confidence":0.87},"adjustments":{"top_left":3.5,"top_right":2.8,"bottom_right":4.1,"bottom_left":3.2,"mean":3.4},"accepted_without_changes":false}
```

## Manual Integration (Inertia/Vue)

For production apps using Inertia.js and Vue, you'll need to integrate the component manually.

### 1. Publish Vue Components

```bash
php artisan vendor:publish --tag=four-corners-components
```

This copies the Vue components to `resources/js/vendor/four-corners/`.

### 2. Create a Laravel Controller

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use RobinsonRyan\FourCorners\Models\DocumentType;
use RobinsonRyan\FourCorners\Models\RejectionReason;
use RobinsonRyan\FourCorners\Services\AnnotationService;

class AnnotationController extends Controller
{
    public function __construct(
        private AnnotationService $annotationService,
    ) {}

    public function show(string $imageId)
    {
        // Fetch your image (from your app's storage)
        $image = YourImageModel::findOrFail($imageId);

        return Inertia::render('Annotations/Annotate', [
            'imageUrl' => $image->url,
            'imageId' => $image->id,
            'documentType' => DocumentType::active()->first(),
            'rejectionReasons' => RejectionReason::active()->ordered()->get(),
            'opencvUrl' => config('four_corners.opencv_url'),
        ]);
    }

    public function complete(Request $request, string $imageId)
    {
        $image = YourImageModel::findOrFail($imageId);

        // Start annotation record
        $annotation = $this->annotationService->start(
            originalImagePath: $image->path,
            originalWidth: $image->width,
            originalHeight: $image->height,
            documentTypeId: $request->input('document_type_id'),
        );

        // Complete with user's adjustments
        $annotation = $this->annotationService->complete(
            annotationId: $annotation->id,
            finalCorners: CornersData::from($request->input('final_corners')),
            finalRotation: (int) $request->input('final_rotation'),
            annotatedBy: auth()->id(),
            timeSpentSeconds: (float) $request->input('time_spent_seconds'),
            displayImageBase64: $request->input('display_image'),
            archiveImageBase64: $request->input('archive_image'),
        );

        return redirect()->route('images.index')
            ->with('success', 'Annotation completed');
    }

    public function reject(Request $request, string $imageId)
    {
        // Handle rejection...
    }
}
```

### 3. Create Vue Page Component

```vue
<!-- resources/js/Pages/Annotations/Annotate.vue -->
<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { DocumentAnnotator } from '@/vendor/four-corners';
import type { CompletePayload, RejectPayload } from '@/vendor/four-corners';

const props = defineProps<{
  imageUrl: string;
  imageId: string;
  documentType: object;
  rejectionReasons: object[];
  opencvUrl: string;
}>();

const handleComplete = async (payload: CompletePayload) => {
  router.post(`/annotations/${props.imageId}/complete`, {
    document_type_id: props.documentType.id,
    final_corners: payload.finalCorners,
    final_rotation: payload.finalRotation,
    time_spent_seconds: payload.timeSpentSeconds,
    display_image: payload.displayImageBase64,
    archive_image: payload.archiveImageBase64,
  });
};

const handleReject = async (payload: RejectPayload) => {
  router.post(`/annotations/${props.imageId}/reject`, {
    rejection_reason_id: payload.rejectionReasonId,
    notes: payload.notes,
    time_spent_seconds: payload.timeSpentSeconds,
  });
};

const handleCancel = () => {
  router.visit('/images');
};
</script>

<template>
  <div class="annotation-page">
    <DocumentAnnotator
      :image-url="imageUrl"
      :document-type="documentType"
      :rejection-reasons="rejectionReasons"
      :opencv-url="opencvUrl"
      @complete="handleComplete"
      @reject="handleReject"
      @cancel="handleCancel"
    />
  </div>
</template>

<style scoped>
.annotation-page {
  height: 100vh;
  display: flex;
  flex-direction: column;
}
</style>
```

### 4. Register Routes

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::get('/annotations/{imageId}', [AnnotationController::class, 'show'])
        ->name('annotations.show');
    Route::post('/annotations/{imageId}/complete', [AnnotationController::class, 'complete'])
        ->name('annotations.complete');
    Route::post('/annotations/{imageId}/reject', [AnnotationController::class, 'reject'])
        ->name('annotations.reject');
});
```

### 5. Listen to Events

Register event listeners to handle image storage:

```php
// app/Providers/EventServiceProvider.php
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

See the [Handling Events](#handling-events) section for listener implementation examples.

### Example Files

For a complete working example, see:
- [`docs/examples/AnnotatePage.vue`](examples/AnnotatePage.vue) - Full Inertia page component
