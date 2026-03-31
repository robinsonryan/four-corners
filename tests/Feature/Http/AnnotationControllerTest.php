<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use RobinsonRyan\FourCorners\Models\DocumentType;
use RobinsonRyan\FourCorners\Models\RejectionReason;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->documentType = DocumentType::create([
        'code' => 'us_drivers_license',
        'name' => "US Driver's License",
        'aspect_ratio_width' => 3.375,
        'aspect_ratio_height' => 2.125,
    ]);

    $this->rejectionReason = RejectionReason::create([
        'code' => 'too_blurry',
        'label' => 'Too Blurry',
        'sort_order' => 1,
    ]);
});

it('returns config with document types and rejection reasons', function (): void {
    $response = $this->getJson(route('four-corners.config'));

    $response->assertOk()
        ->assertJsonStructure([
            'document_types' => [
                '*' => ['id', 'code', 'name', 'aspect_ratio_width', 'aspect_ratio_height'],
            ],
            'rejection_reasons' => [
                '*' => ['id', 'code', 'label'],
            ],
            'opencv_url',
            'output',
        ]);
});

it('creates a new annotation', function (): void {
    $response = $this->postJson(route('four-corners.start'), [
        'original_image_path' => 's3://bucket/images/test.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $this->documentType->id,
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'annotation' => [
                'id',
                'original_image_path',
                'original_width',
                'original_height',
                'status',
            ],
        ]);
});

it('validates required fields when starting annotation', function (): void {
    $response = $this->postJson(route('four-corners.start'), []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'original_image_path',
            'original_width',
            'original_height',
            'document_type_id',
        ]);
});

it('returns annotation by id', function (): void {
    $createResponse = $this->postJson(route('four-corners.start'), [
        'original_image_path' => 's3://bucket/images/test.jpg',
        'original_width' => 1200,
        'original_height' => 800,
        'document_type_id' => $this->documentType->id,
    ]);

    $annotationId = $createResponse->json('annotation.id');

    $response = $this->getJson(route('four-corners.show', $annotationId));

    $response->assertOk()
        ->assertJsonPath('annotation.id', $annotationId);
});

it('returns 404 for non-existent annotation', function (): void {
    $response = $this->getJson(route('four-corners.show', 'non-existent-id'));

    $response->assertNotFound();
});
