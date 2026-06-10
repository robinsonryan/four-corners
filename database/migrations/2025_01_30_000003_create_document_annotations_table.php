<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RobinsonRyan\FourCorners\Support\TablePrefixer;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(TablePrefixer::prefix('document_annotations'), function (Blueprint $table): void {
            if (config('four_corners.id_type') === 'uuid7') {
                $table->uuid('id')->primary();
            } else {
                $table->id();
            }

            // Source image (path/URL provided by consuming app)
            $table->string('original_image_path', 500);
            $table->unsignedInteger('original_width');
            $table->unsignedInteger('original_height');

            // Document type relationship
            if (config('four_corners.id_type') === 'uuid7') {
                $table->uuid('document_type_id');
                $table->foreign('document_type_id')
                    ->references('id')
                    ->on(TablePrefixer::prefix('document_types'))
                    ->cascadeOnDelete();
            } else {
                $table->foreignId('document_type_id')
                    ->constrained(TablePrefixer::prefix('document_types'))
                    ->cascadeOnDelete();
            }

            // Status: pending, processing, processed, rejected
            $table->string('status', 20)->default('pending');

            // Auto-detection results (nullable - may not always run)
            $table->json('auto_detection')->nullable();

            // Final annotation (for processed status)
            $table->json('final_corners')->nullable();
            $table->unsignedSmallInteger('final_rotation')->default(0);

            // Comparison metrics (computed on save)
            $table->boolean('accepted_without_changes')->nullable();
            $table->json('corner_adjustments')->nullable();
            $table->boolean('rotation_was_correct')->nullable();

            // Output images (paths returned to consuming app)
            $table->string('display_image_path', 500)->nullable();
            $table->string('archive_image_path', 500)->nullable();

            // Rejection relationship
            if (config('four_corners.id_type') === 'uuid7') {
                $table->uuid('rejection_reason_id')->nullable();
                $table->foreign('rejection_reason_id')
                    ->references('id')
                    ->on(TablePrefixer::prefix('rejection_reasons'))
                    ->nullOnDelete();
            } else {
                $table->foreignId('rejection_reason_id')
                    ->nullable()
                    ->constrained(TablePrefixer::prefix('rejection_reasons'))
                    ->nullOnDelete();
            }
            $table->text('rejection_notes')->nullable();

            // Audit fields (no foreign key — the consuming app owns the users table)
            if (config('four_corners.id_type') === 'uuid7') {
                $table->uuid('annotated_by')->nullable();
            } else {
                $table->unsignedBigInteger('annotated_by')->nullable();
            }
            $table->timestamp('annotated_at')->nullable();
            $table->decimal('time_spent_seconds', 8, 2)->nullable();

            // Tenant support (no foreign key — the consuming app owns the tenant table)
            if (config('four_corners.tenant.enabled')) {
                if (config('four_corners.id_type') === 'uuid7') {
                    $table->uuid(config('four_corners.tenant.column', 'tenant_id'))->nullable();
                } else {
                    $table->unsignedBigInteger(config('four_corners.tenant.column', 'tenant_id'))->nullable();
                }
                $table->index(config('four_corners.tenant.column', 'tenant_id'));
            }

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('annotated_at');
            $table->index('annotated_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(TablePrefixer::prefix('document_annotations'));
    }
};
