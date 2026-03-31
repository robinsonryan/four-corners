# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Four Corners is a Laravel package for document annotation with bounding box selection, perspective transformation, and ML training data collection. It provides client-side image processing using OpenCV.js and Vue 3 components for the annotation interface.

**Namespace:** `RobinsonRyan\FourCorners`
**PHP:** 8.3+
**Laravel:** 11.x, 12.x
**Node:** 18+
**Vue:** 3.5+

## Development Commands

### PHP Commands

```bash
# Install dependencies
composer install

# Run tests
composer test

# Run single test
./vendor/bin/pest --filter="test name"

# Run tests with coverage
composer test:coverage

# Static analysis (PHPStan level 8)
composer analyze

# Code formatting (Laravel Pint)
composer lint

# Full quality check (lint, analyze, test)
composer quality
```

### Frontend Commands

```bash
# Install dependencies
npm install

# Run tests
npm test

# Run tests in watch mode
npm run test:watch

# TypeScript type checking
npm run typecheck
```

### DDEV Commands

```bash
ddev start           # Start environment
ddev test            # Run PHP tests
ddev quality         # Full PHP quality checks
```

## Architecture

### Directory Structure

```
src/
├── Concerns/                    # Traits
│   └── ConfiguresIdentifiers.php   # UUID7/incrementing ID support
├── Console/Commands/            # Artisan commands
│   └── ExportTrainingDataCommand.php
├── Contracts/                   # Interfaces
│   ├── AnnotationRepositoryInterface.php
│   └── ImageProcessorInterface.php
├── Data/                        # Spatie Laravel Data DTOs
│   ├── AdjustmentMetricsData.php
│   ├── AutoDetectionData.php
│   ├── CornersData.php
│   ├── PointData.php
│   └── RejectionData.php
├── Enums/                       # PHP enums
│   ├── AnnotationStatus.php     # pending, processing, processed, rejected
│   ├── DetectionMethod.php      # opencv_js_contour_v1, ml_model_v1, manual
│   └── Rotation.php             # 0, 90, 180, 270
├── Events/                      # Laravel events
│   ├── AnnotationCompleted.php
│   ├── AnnotationRejected.php
│   ├── AnnotationStarted.php
│   ├── CornersAdjusted.php
│   └── TrainingDataExported.php
├── Exceptions/                  # Custom exceptions
│   ├── AnnotationNotFoundException.php
│   ├── InvalidCornersException.php
│   └── ProcessingFailedException.php
├── Http/
│   ├── Controllers/
│   │   ├── AnnotationController.php
│   │   └── DemoController.php
│   ├── Requests/
│   │   ├── CompleteAnnotationRequest.php
│   │   ├── RejectAnnotationRequest.php
│   │   └── StartAnnotationRequest.php
│   └── Resources/
│       └── AnnotationResource.php
├── Jobs/
│   └── ProcessAnnotationJob.php
├── Models/
│   ├── DocumentAnnotation.php   # Main annotation model
│   ├── DocumentType.php         # Document type definitions
│   └── RejectionReason.php      # Rejection categories
├── Repositories/
│   └── EloquentAnnotationRepository.php
├── Services/
│   ├── AnnotationService.php    # Main service facade
│   ├── MetricsCalculator.php    # Corner adjustment metrics
│   └── TrainingDataExporter.php # JSONL export
└── FourCornersServiceProvider.php

resources/js/
├── Components/
│   ├── DocumentAnnotator.vue    # Main annotation interface
│   ├── CornerHandle.vue         # Draggable corner points
│   ├── QuadrilateralOverlay.vue # Selection polygon
│   ├── RotationControls.vue     # Rotation buttons
│   ├── ZoomControls.vue         # Zoom interface
│   ├── ActionButtons.vue        # Accept/Reject/Preview
│   ├── RejectModal.vue          # Rejection dialog
│   └── PreviewModal.vue         # Transform preview
├── Composables/
│   ├── useAnnotationState.ts    # State management
│   ├── useCornerDetection.ts    # OpenCV.js contour detection
│   ├── useOpenCV.ts             # OpenCV.js loader
│   └── usePerspectiveTransform.ts # Perspective warp
├── Types/
│   └── index.ts                 # TypeScript interfaces
└── index.ts                     # Package exports
```

### Key Components

| Component | Purpose |
|-----------|---------|
| `AnnotationService` | Main service for annotation lifecycle (start, complete, reject) |
| `MetricsCalculator` | Calculate pixel distance between suggested and final corners |
| `TrainingDataExporter` | Export JSONL format for ML training |
| `DocumentAnnotation` | Main model storing annotation state and metadata |
| `DocumentType` | Document type definitions (aspect ratio, output sizes) |
| `RejectionReason` | Categorized rejection reasons for unusable images |

### Data Flow

1. **Start**: Create annotation with image path and dimensions
2. **Auto-detect**: Client-side OpenCV.js detects document corners
3. **Annotate**: User drags corner handles to adjust positions
4. **Preview**: Client generates perspective-corrected preview
5. **Complete**: Client sends base64 images, backend dispatches event
6. **Store**: Consuming app handles image storage via `AnnotationCompleted` event

### Events

| Event | When Fired | Payload |
|-------|------------|---------|
| `AnnotationStarted` | New annotation created | `$annotation` |
| `AnnotationCompleted` | Annotation processed | `$annotation`, `$displayImageBase64`, `$archiveImageBase64` |
| `AnnotationRejected` | Image rejected | `$annotation` |
| `CornersAdjusted` | User modified corners | `$annotation`, `$adjustments` |
| `TrainingDataExported` | Batch export completed | `$count`, `$path` |

### Configuration Pattern

Uses `ConfiguresIdentifiers` trait for flexible primary keys:
- `id_type`: `'incrementing'` or `'uuid7'`
- `table_prefix`: Optional prefix for all tables
- `tenant.enabled`: Multi-tenant scoping support

## Testing

### PHP Tests

Uses Pest with Orchestra Testbench. Tests run against SQLite in-memory.

```
tests/
├── Feature/
│   ├── Http/                 # Controller tests
│   └── Models/               # Model integration tests
├── Unit/
│   ├── Data/                 # DTO tests
│   └── Services/             # Service unit tests
└── TestCase.php              # Base test case with config
```

Key test patterns:
- Models use `RefreshDatabase` trait
- DTOs are tested for validation and transformation
- Services are tested with mocked repositories
- HTTP tests use `actingAs()` for auth

### Frontend Tests

Uses Vitest with Vue Test Utils and happy-dom.

```
resources/js/__tests__/
├── useAnnotationState.spec.ts
├── Types.spec.ts
├── RotationControls.spec.ts
├── ZoomControls.spec.ts
├── ActionButtons.spec.ts
└── RejectModal.spec.ts
```

## Frontend Architecture

### Vue Components

Components use Konva.js (via vue-konva) for canvas rendering:

```vue
<v-stage :config="stageConfig">
  <v-layer>
    <v-image :config="imageConfig" />
    <QuadrilateralOverlay :corners="scaledCorners" />
    <CornerHandle
      v-for="key in cornerKeys"
      :corner-key="key"
      :position="scaledCorners[key]"
      @drag-move="handleCornerDrag"
    />
  </v-layer>
</v-stage>
```

### Composables

- `useOpenCV(url)` - Loads OpenCV.js from CDN, caches instance
- `useCornerDetection()` - Canny edge detection + contour finding
- `usePerspectiveTransform()` - 4-point perspective warp
- `useAnnotationState(corners?, rotation?)` - Reactive state with change tracking

### TypeScript Types

```typescript
interface Corners {
  topLeft: Point;
  topRight: Point;
  bottomRight: Point;
  bottomLeft: Point;
}

interface CompletePayload {
  annotationId: string | null;
  finalCorners: Corners;
  finalRotation: Rotation;
  timeSpentSeconds: number;
  displayImageBase64: string;
  archiveImageBase64: string;
  autoDetection: AutoDetection | null;
}
```

## Common Tasks

### Adding a New Document Type

1. Create migration or seed:
```php
DocumentType::create([
    'code' => 'new_document',
    'name' => 'New Document',
    'aspect_ratio_width' => 800,
    'aspect_ratio_height' => 600,
    'display_width' => 400,
    'display_height' => 300,
    'archive_width' => 800,
    'archive_height' => 600,
]);
```

### Adding a New Rejection Reason

```php
RejectionReason::create([
    'code' => 'new_reason',
    'label' => 'New Reason',
    'description' => 'Description shown to user',
    'sort_order' => 10,
]);
```

### Customizing Corner Detection

Override the detection algorithm in a custom composable:
```typescript
// resources/js/Composables/useCustomDetection.ts
export function useCustomDetection() {
  const detect = async (image, cv) => {
    // Custom OpenCV.js detection logic
  };
  return { detect };
}
```

## Documentation

- [Installation](docs/installation.md) - Setup and requirements
- [Configuration](docs/configuration.md) - All configuration options
- [Usage](docs/usage.md) - Backend and frontend usage examples
