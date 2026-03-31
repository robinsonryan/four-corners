<?php

declare(strict_types=1);

use RobinsonRyan\FourCorners\Enums\AnnotationStatus;
use RobinsonRyan\FourCorners\Models\DocumentAnnotation;
use RobinsonRyan\FourCorners\Models\DocumentType;
use RobinsonRyan\FourCorners\Models\RejectionReason;

beforeEach(function (): void {
    $this->documentType = DocumentType::create([
        'code' => 'us_drivers_license',
        'name' => "US Driver's License",
        'aspect_ratio_width' => 3.375,
        'aspect_ratio_height' => 2.125,
    ]);
});

it('can create a document annotation', function (): void {
    $annotation = DocumentAnnotation::create([
        'original_image_path' => 's3://bucket/images/test.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $this->documentType->id,
        'status' => AnnotationStatus::Pending,
    ]);

    expect($annotation->exists)->toBeTrue()
        ->and($annotation->fresh()->status)->toBe(AnnotationStatus::Pending)
        ->and($annotation->original_image_path)->toBe('s3://bucket/images/test.jpg');
});

it('casts status to enum', function (): void {
    $annotation = DocumentAnnotation::create([
        'original_image_path' => 's3://bucket/images/test.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $this->documentType->id,
        'status' => 'pending',
    ]);

    expect($annotation->status)->toBeInstanceOf(AnnotationStatus::class)
        ->and($annotation->status)->toBe(AnnotationStatus::Pending);
});

it('belongs to a document type', function (): void {
    $annotation = DocumentAnnotation::create([
        'original_image_path' => 's3://bucket/images/test.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $this->documentType->id,
    ]);

    expect($annotation->documentType)->toBeInstanceOf(DocumentType::class)
        ->and($annotation->documentType->code)->toBe('us_drivers_license');
});

it('belongs to a rejection reason when rejected', function (): void {
    $reason = RejectionReason::create([
        'code' => 'too_blurry',
        'label' => 'Too Blurry',
        'sort_order' => 1,
    ]);

    $annotation = DocumentAnnotation::create([
        'original_image_path' => 's3://bucket/images/test.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $this->documentType->id,
        'status' => AnnotationStatus::Rejected,
        'rejection_reason_id' => $reason->id,
    ]);

    expect($annotation->rejectionReason)->toBeInstanceOf(RejectionReason::class)
        ->and($annotation->rejectionReason->code)->toBe('too_blurry');
});

it('casts json fields correctly', function (): void {
    $annotation = DocumentAnnotation::create([
        'original_image_path' => 's3://bucket/images/test.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $this->documentType->id,
        'auto_detection' => [
            'method' => 'opencv_js_contour_v1',
            'confidence' => 0.85,
        ],
        'final_corners' => [
            'top_left' => ['x' => 100, 'y' => 100],
            'top_right' => ['x' => 900, 'y' => 100],
            'bottom_right' => ['x' => 900, 'y' => 600],
            'bottom_left' => ['x' => 100, 'y' => 600],
        ],
    ]);

    expect($annotation->auto_detection)->toBeArray()
        ->and($annotation->auto_detection['confidence'])->toBe(0.85)
        ->and($annotation->final_corners)->toBeArray()
        ->and($annotation->final_corners['top_left']['x'])->toBe(100);
});

it('scopes to pending annotations', function (): void {
    DocumentAnnotation::create([
        'original_image_path' => 's3://bucket/images/pending.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $this->documentType->id,
        'status' => AnnotationStatus::Pending,
    ]);

    DocumentAnnotation::create([
        'original_image_path' => 's3://bucket/images/processed.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $this->documentType->id,
        'status' => AnnotationStatus::Processed,
    ]);

    expect(DocumentAnnotation::pending()->count())->toBe(1)
        ->and(DocumentAnnotation::pending()->first()->original_image_path)->toContain('pending');
});

it('scopes to processed annotations', function (): void {
    DocumentAnnotation::create([
        'original_image_path' => 's3://bucket/images/pending.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $this->documentType->id,
        'status' => AnnotationStatus::Pending,
    ]);

    DocumentAnnotation::create([
        'original_image_path' => 's3://bucket/images/processed.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $this->documentType->id,
        'status' => AnnotationStatus::Processed,
    ]);

    expect(DocumentAnnotation::processed()->count())->toBe(1);
});

it('scopes to rejected annotations', function (): void {
    DocumentAnnotation::create([
        'original_image_path' => 's3://bucket/images/pending.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $this->documentType->id,
        'status' => AnnotationStatus::Pending,
    ]);

    DocumentAnnotation::create([
        'original_image_path' => 's3://bucket/images/rejected.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $this->documentType->id,
        'status' => AnnotationStatus::Rejected,
    ]);

    expect(DocumentAnnotation::rejected()->count())->toBe(1);
});

it('scopes by document type', function (): void {
    $otherType = DocumentType::create([
        'code' => 'us_passport',
        'name' => 'US Passport',
        'aspect_ratio_width' => 5.0,
        'aspect_ratio_height' => 3.5,
    ]);

    DocumentAnnotation::create([
        'original_image_path' => 's3://bucket/images/license.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $this->documentType->id,
    ]);

    DocumentAnnotation::create([
        'original_image_path' => 's3://bucket/images/passport.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $otherType->id,
    ]);

    expect(DocumentAnnotation::forDocumentType($this->documentType->id)->count())->toBe(1);
});
