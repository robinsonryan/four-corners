<?php

declare(strict_types=1);

use RobinsonRyan\FourCorners\Models\DocumentType;

it('can create a document type', function (): void {
    $documentType = DocumentType::create([
        'code' => 'us_drivers_license',
        'name' => "US Driver's License",
        'aspect_ratio_width' => 3.375,
        'aspect_ratio_height' => 2.125,
        'display_width' => 850,
        'display_height' => 536,
        'archive_width' => 1700,
        'archive_height' => 1072,
        'is_active' => true,
    ]);

    expect($documentType->exists)->toBeTrue()
        ->and($documentType->code)->toBe('us_drivers_license')
        ->and($documentType->name)->toBe("US Driver's License")
        ->and($documentType->fresh()->is_active)->toBeTrue();
});

it('uses uuid7 for primary key when configured', function (): void {
    config(['four_corners.id_type' => 'uuid7']);

    $documentType = DocumentType::create([
        'code' => 'test_doc',
        'name' => 'Test Document',
        'aspect_ratio_width' => 3.375,
        'aspect_ratio_height' => 2.125,
    ]);

    expect($documentType->id)->toBeString()
        ->and(strlen((string) $documentType->id))->toBe(36);
});

it('scopes to active document types', function (): void {
    DocumentType::create([
        'code' => 'active_doc',
        'name' => 'Active Document',
        'aspect_ratio_width' => 3.375,
        'aspect_ratio_height' => 2.125,
        'is_active' => true,
    ]);

    DocumentType::create([
        'code' => 'inactive_doc',
        'name' => 'Inactive Document',
        'aspect_ratio_width' => 3.375,
        'aspect_ratio_height' => 2.125,
        'is_active' => false,
    ]);

    expect(DocumentType::active()->count())->toBe(1)
        ->and(DocumentType::active()->first()->code)->toBe('active_doc');
});

it('has annotations relationship', function (): void {
    $documentType = DocumentType::create([
        'code' => 'test_doc',
        'name' => 'Test Document',
        'aspect_ratio_width' => 3.375,
        'aspect_ratio_height' => 2.125,
    ]);

    expect($documentType->annotations())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class);
});
