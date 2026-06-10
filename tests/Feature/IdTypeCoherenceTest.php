<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RobinsonRyan\FourCorners\Models\DocumentAnnotation;
use RobinsonRyan\FourCorners\Models\DocumentType;
use RobinsonRyan\FourCorners\Models\RejectionReason;
use RobinsonRyan\FourCorners\Support\TablePrefixer;

function createDocumentType(): DocumentType
{
    return DocumentType::create([
        'code' => 'us_drivers_license',
        'name' => "US Driver's License",
        'aspect_ratio_width' => 3.375,
        'aspect_ratio_height' => 2.125,
    ]);
}

/**
 * @param  array<string, mixed>  $config
 */
function remigrateWith(array $config): void
{
    config($config);

    Schema::dropAllTables();

    test()->artisan('migrate', [
        '--path' => realpath(__DIR__.'/../../database/migrations'),
        '--realpath' => true,
    ])->run();
}

describe('uuid7 mode', function (): void {
    it('builds annotated_by as a uuid column', function (): void {
        $type = Schema::getColumnType(TablePrefixer::prefix('document_annotations'), 'annotated_by');

        expect($type)->toBe('varchar');
    });

    it('round-trips a uuid annotated_by unmangled through create and retrieve', function (): void {
        $documentType = createDocumentType();
        $userId = Str::uuid7()->toString();

        $annotation = DocumentAnnotation::create([
            'original_image_path' => 's3://bucket/images/test.jpg',
            'original_width' => 1200,
            'original_height' => 800,
            'document_type_id' => $documentType->id,
            'annotated_by' => $userId,
        ]);

        $fresh = DocumentAnnotation::query()->findOrFail($annotation->id);

        expect($fresh->annotated_by)->toBeString()
            ->and($fresh->annotated_by)->toBe($userId);
    });

    it('builds the tenant column as uuid when tenancy is enabled', function (): void {
        remigrateWith([
            'four_corners.id_type' => 'uuid7',
            'four_corners.tenant.enabled' => true,
        ]);

        $type = Schema::getColumnType(TablePrefixer::prefix('document_annotations'), 'tenant_id');

        expect($type)->toBe('varchar');
    });

    it('stores the authenticated uuid user id unmangled when rejecting via http', function (): void {
        $documentType = createDocumentType();
        $reason = RejectionReason::create([
            'code' => 'too_blurry',
            'label' => 'Too Blurry',
            'sort_order' => 1,
        ]);
        $annotation = DocumentAnnotation::create([
            'original_image_path' => 's3://bucket/images/test.jpg',
            'original_width' => 1200,
            'original_height' => 800,
            'document_type_id' => $documentType->id,
        ]);

        $userId = Str::uuid7()->toString();
        $this->actingAs(new GenericUser(['id' => $userId]));

        $response = $this->postJson(route('four-corners.reject', $annotation->id), [
            'rejection_reason_id' => $reason->id,
            'notes' => 'Too blurry to read',
            'time_spent_seconds' => 4.2,
        ]);

        $response->assertOk()
            ->assertJsonPath('annotation.annotated_by', $userId);

        expect($annotation->fresh()->annotated_by)->toBe($userId);
    });
});

describe('incrementing mode', function (): void {
    it('builds annotated_by as a big integer column', function (): void {
        remigrateWith(['four_corners.id_type' => 'incrementing']);

        $type = Schema::getColumnType(TablePrefixer::prefix('document_annotations'), 'annotated_by');

        expect($type)->toBe('integer');
    });

    it('round-trips an integer annotated_by through create and retrieve', function (): void {
        remigrateWith(['four_corners.id_type' => 'incrementing']);

        $documentType = createDocumentType();

        $annotation = DocumentAnnotation::create([
            'original_image_path' => 's3://bucket/images/test.jpg',
            'original_width' => 1200,
            'original_height' => 800,
            'document_type_id' => $documentType->id,
            'annotated_by' => 42,
        ]);

        $fresh = DocumentAnnotation::query()->findOrFail($annotation->id);

        expect($fresh->annotated_by)->toBeInt()
            ->and($fresh->annotated_by)->toBe(42);
    });

    it('builds the tenant column as big integer when tenancy is enabled', function (): void {
        remigrateWith([
            'four_corners.id_type' => 'incrementing',
            'four_corners.tenant.enabled' => true,
        ]);

        $type = Schema::getColumnType(TablePrefixer::prefix('document_annotations'), 'tenant_id');

        expect($type)->toBe('integer');
    });

    it('casts the authenticated user id to int when rejecting via http', function (): void {
        remigrateWith(['four_corners.id_type' => 'incrementing']);

        $documentType = createDocumentType();
        $reason = RejectionReason::create([
            'code' => 'too_blurry',
            'label' => 'Too Blurry',
            'sort_order' => 1,
        ]);
        $annotation = DocumentAnnotation::create([
            'original_image_path' => 's3://bucket/images/test.jpg',
            'original_width' => 1200,
            'original_height' => 800,
            'document_type_id' => $documentType->id,
        ]);

        $this->actingAs(new GenericUser(['id' => '7']));

        $response = $this->postJson(route('four-corners.reject', $annotation->id), [
            'rejection_reason_id' => $reason->id,
            'notes' => null,
            'time_spent_seconds' => 4.2,
        ]);

        $response->assertOk()
            ->assertJsonPath('annotation.annotated_by', 7);

        expect($annotation->fresh()->annotated_by)->toBeInt()
            ->and($annotation->fresh()->annotated_by)->toBe(7);
    });
});
