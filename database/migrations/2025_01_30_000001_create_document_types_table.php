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
        Schema::create(TablePrefixer::prefix('document_types'), function (Blueprint $table): void {
            if (config('four_corners.id_type') === 'uuid7') {
                $table->uuid('id')->primary();
            } else {
                $table->id();
            }

            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->decimal('aspect_ratio_width', 6, 3);
            $table->decimal('aspect_ratio_height', 6, 3);
            $table->unsignedInteger('display_width')->default(850);
            $table->unsignedInteger('display_height')->default(536);
            $table->unsignedInteger('archive_width')->default(1700);
            $table->unsignedInteger('archive_height')->default(1072);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(TablePrefixer::prefix('document_types'));
    }
};
