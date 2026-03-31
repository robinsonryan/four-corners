<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Services;

use Illuminate\Support\Carbon;
use RobinsonRyan\FourCorners\Enums\AnnotationStatus;
use RobinsonRyan\FourCorners\Events\TrainingDataExported;
use RobinsonRyan\FourCorners\Models\DocumentAnnotation;
use RobinsonRyan\FourCorners\Models\DocumentType;
use RuntimeException;

final class TrainingDataExporter
{
    /**
     * Export annotation data for ML training.
     */
    public function export(
        string $outputPath,
        ?Carbon $from = null,
        ?Carbon $to = null,
        ?string $documentTypeCode = null,
        bool $includeRejected = false,
    ): int {
        $query = DocumentAnnotation::query()
            ->with('documentType');

        // Filter by status
        if ($includeRejected) {
            $query->whereIn('status', [AnnotationStatus::Processed, AnnotationStatus::Rejected]);
        } else {
            $query->where('status', AnnotationStatus::Processed);
        }

        // Filter by date range
        if ($from !== null) {
            $query->where('annotated_at', '>=', $from);
        }

        if ($to !== null) {
            $query->where('annotated_at', '<=', $to);
        }

        // Filter by document type
        if ($documentTypeCode !== null) {
            $documentType = DocumentType::where('code', $documentTypeCode)->first();
            if ($documentType !== null) {
                $query->where('document_type_id', $documentType->id);
            }
        }

        $count = 0;
        $handle = fopen($outputPath, 'w');

        if ($handle === false) {
            throw new RuntimeException("Cannot open file for writing: {$outputPath}");
        }

        $query->chunk(100, function ($annotations) use ($handle, &$count): void {
            foreach ($annotations as $annotation) {
                $data = $this->formatAnnotation($annotation);
                fwrite($handle, json_encode($data, JSON_UNESCAPED_SLASHES)."\n");
                $count++;
            }
        });

        fclose($handle);

        event(new TrainingDataExported($outputPath, $count, 'jsonl'));

        return $count;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatAnnotation(DocumentAnnotation $annotation): array
    {
        return [
            'id' => $annotation->id,
            'original_image' => $annotation->original_image_path,
            'original_width' => $annotation->original_width,
            'original_height' => $annotation->original_height,
            'corners' => $annotation->final_corners,
            'rotation' => $annotation->final_rotation,
            'document_type' => $annotation->documentType->code,
            'status' => $annotation->status->value,
            'auto_detection_method' => $annotation->auto_detection['method'] ?? null,
            'auto_detection_confidence' => $annotation->auto_detection['confidence'] ?? null,
            'accepted_without_changes' => $annotation->accepted_without_changes,
            'adjustment_mean_px' => $annotation->corner_adjustments['mean'] ?? null,
            'rotation_was_correct' => $annotation->rotation_was_correct,
            'rejection_reason' => $annotation->rejectionReason?->code,
            'annotated_at' => $annotation->annotated_at?->toIso8601String(),
        ];
    }
}
