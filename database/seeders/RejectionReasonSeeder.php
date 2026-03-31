<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Database\Seeders;

use Illuminate\Database\Seeder;
use RobinsonRyan\FourCorners\Models\RejectionReason;

final class RejectionReasonSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            ['code' => 'too_blurry', 'label' => 'Too Blurry', 'description' => 'Motion blur or out of focus', 'sort_order' => 1],
            ['code' => 'too_dark', 'label' => 'Too Dark', 'description' => 'Underexposed, details not visible', 'sort_order' => 2],
            ['code' => 'too_bright', 'label' => 'Too Bright/Washed Out', 'description' => 'Overexposed, washed out', 'sort_order' => 3],
            ['code' => 'partially_obscured', 'label' => 'Partially Obscured', 'description' => 'Finger, glare, or object blocking part of document', 'sort_order' => 4],
            ['code' => 'wrong_document', 'label' => 'Wrong Document Type', 'description' => 'Not the expected document type', 'sort_order' => 5],
            ['code' => 'document_damaged', 'label' => 'Document Damaged', 'description' => 'Physical damage visible on the ID', 'sort_order' => 6],
            ['code' => 'image_corrupted', 'label' => 'Image Corrupted', 'description' => 'File could not be loaded or processed', 'sort_order' => 7],
            ['code' => 'multiple_documents', 'label' => 'Multiple Documents', 'description' => 'More than one document visible in image', 'sort_order' => 8],
            ['code' => 'wrong_orientation', 'label' => 'Cannot Determine Orientation', 'description' => 'Unable to determine correct document orientation', 'sort_order' => 9],
            ['code' => 'other', 'label' => 'Other', 'description' => 'See notes for details', 'sort_order' => 100],
        ];

        foreach ($reasons as $reason) {
            RejectionReason::updateOrCreate(
                ['code' => $reason['code']],
                $reason,
            );
        }
    }
}
