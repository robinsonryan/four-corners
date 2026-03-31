<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds all Four Corners default data.
 *
 * This seeder combines DocumentTypeSeeder and RejectionReasonSeeder
 * for easy setup of the package.
 *
 * Usage:
 *   php artisan db:seed --class="RobinsonRyan\\FourCorners\\Database\\Seeders\\FourCornersSeeder"
 *
 * Or after publishing seeders:
 *   php artisan db:seed --class=FourCornersSeeder
 */
final class FourCornersSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DocumentTypeSeeder::class,
            RejectionReasonSeeder::class,
        ]);
    }
}
