<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use RobinsonRyan\FourCorners\Data\AutoDetectionData;
use RobinsonRyan\FourCorners\Data\CornersData;
use RobinsonRyan\FourCorners\Data\PointData;
use RobinsonRyan\FourCorners\Enums\AnnotationStatus;
use RobinsonRyan\FourCorners\Enums\DetectionMethod;
use RobinsonRyan\FourCorners\Events\AnnotationRejected;
use RobinsonRyan\FourCorners\Events\AnnotationStarted;
use RobinsonRyan\FourCorners\Models\DocumentAnnotation;
use RobinsonRyan\FourCorners\Models\DocumentType;
use RobinsonRyan\FourCorners\Models\RejectionReason;
use RobinsonRyan\FourCorners\Services\AnnotationService;

beforeEach(function (): void {
    $this->documentType = DocumentType::create([
        'code' => 'us_drivers_license',
        'name' => "US Driver's License",
        'aspect_ratio_width' => 3.375,
        'aspect_ratio_height' => 2.125,
    ]);

    $this->service = app(AnnotationService::class);
});

it('starts a new annotation and dispatches event', function (): void {
    Event::fake([AnnotationStarted::class]);

    $annotation = $this->service->start(
        originalImagePath: 's3://bucket/images/test.jpg',
        originalWidth: 1200,
        originalHeight: 800,
        documentTypeId: $this->documentType->id,
    );

    expect($annotation)->toBeInstanceOf(DocumentAnnotation::class)
        ->and($annotation->status)->toBe(AnnotationStatus::Pending)
        ->and($annotation->original_image_path)->toBe('s3://bucket/images/test.jpg');

    Event::assertDispatched(AnnotationStarted::class, fn ($event): bool => $event->annotation->id === $annotation->id);
});

it('starts annotation with auto-detection data', function (): void {
    $autoDetection = new AutoDetectionData(
        method: DetectionMethod::OpenCVContour,
        corners: new CornersData(
            top_left: new PointData(x: 100, y: 100),
            top_right: new PointData(x: 900, y: 100),
            bottom_right: new PointData(x: 900, y: 600),
            bottom_left: new PointData(x: 100, y: 600),
        ),
        rotation_suggestion: 0,
        confidence: 0.85,
        detection_time_ms: 150,
    );

    $annotation = $this->service->start(
        originalImagePath: 's3://bucket/images/test.jpg',
        originalWidth: 1200,
        originalHeight: 800,
        documentTypeId: $this->documentType->id,
        autoDetection: $autoDetection,
    );

    expect($annotation->auto_detection)->toBeArray()
        ->and($annotation->auto_detection['method'])->toBe('opencv_js_contour_v1')
        ->and($annotation->auto_detection['confidence'])->toBe(0.85);
});

it('rejects an annotation and dispatches event', function (): void {
    Event::fake([AnnotationRejected::class]);

    $reason = RejectionReason::create([
        'code' => 'too_blurry',
        'label' => 'Too Blurry',
        'sort_order' => 1,
    ]);

    $annotation = $this->service->start(
        originalImagePath: 's3://bucket/images/test.jpg',
        originalWidth: 1200,
        originalHeight: 800,
        documentTypeId: $this->documentType->id,
    );

    $rejected = $this->service->reject(
        annotationId: $annotation->id,
        rejectionReasonId: $reason->id,
        notes: 'Image is very blurry',
        rejectedBy: 1,
        timeSpentSeconds: 5.5,
    );

    expect($rejected->status)->toBe(AnnotationStatus::Rejected)
        ->and($rejected->rejection_reason_id)->toBe($reason->id)
        ->and($rejected->rejection_notes)->toBe('Image is very blurry')
        ->and($rejected->annotated_by)->toBe(1)
        ->and($rejected->time_spent_seconds)->toBe(5.5);

    Event::assertDispatched(AnnotationRejected::class);
});

it('gets pending annotations by document type', function (): void {
    // Create pending annotations
    $this->service->start(
        originalImagePath: 's3://bucket/images/pending1.jpg',
        originalWidth: 1200,
        originalHeight: 800,
        documentTypeId: $this->documentType->id,
    );

    $this->service->start(
        originalImagePath: 's3://bucket/images/pending2.jpg',
        originalWidth: 1200,
        originalHeight: 800,
        documentTypeId: $this->documentType->id,
    );

    // Create a processed annotation
    $processed = DocumentAnnotation::create([
        'original_image_path' => 's3://bucket/images/processed.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $this->documentType->id,
        'status' => AnnotationStatus::Processed,
    ]);

    $pending = $this->service->getPending($this->documentType->id);

    expect($pending)->toHaveCount(2);
});
