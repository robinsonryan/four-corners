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
        Schema::create(TablePrefixer::prefix('rejection_reasons'), function (Blueprint $table): void {
            if (config('four_corners.id_type') === 'uuid7') {
                $table->uuid('id')->primary();
            } else {
                $table->id();
            }

            $table->string('code', 50)->unique();
            $table->string('label', 100);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(TablePrefixer::prefix('rejection_reasons'));
    }
};
