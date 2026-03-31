<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Database\Seeders;

use Illuminate\Database\Seeder;
use RobinsonRyan\FourCorners\Models\DocumentType;

final class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'us_drivers_license',
                'name' => "US Driver's License",
                'aspect_ratio_width' => 3.375,
                'aspect_ratio_height' => 2.125,
                'display_width' => 850,
                'display_height' => 536,
                'archive_width' => 1700,
                'archive_height' => 1072,
            ],
            [
                'code' => 'us_passport',
                'name' => 'US Passport',
                'aspect_ratio_width' => 5.0,
                'aspect_ratio_height' => 3.5,
                'display_width' => 850,
                'display_height' => 595,
                'archive_width' => 1700,
                'archive_height' => 1190,
            ],
            [
                'code' => 'us_passport_card',
                'name' => 'US Passport Card',
                'aspect_ratio_width' => 3.375,
                'aspect_ratio_height' => 2.125,
                'display_width' => 850,
                'display_height' => 536,
                'archive_width' => 1700,
                'archive_height' => 1072,
            ],
        ];

        foreach ($types as $type) {
            DocumentType::updateOrCreate(
                ['code' => $type['code']],
                $type,
            );
        }
    }
}
