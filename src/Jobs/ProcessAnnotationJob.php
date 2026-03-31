<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RobinsonRyan\FourCorners\Contracts\AnnotationRepositoryInterface;
use RobinsonRyan\FourCorners\Data\AdjustmentMetricsData;
use RobinsonRyan\FourCorners\Data\CornersData;
use RobinsonRyan\FourCorners\Enums\AnnotationStatus;
use RobinsonRyan\FourCorners\Events\AnnotationCompleted;

final class ProcessAnnotationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $annotationId,
        public CornersData $finalCorners,
        public int $finalRotation,
        public int $annotatedBy,
        public float $timeSpentSeconds,
        public string $displayImageBase64,
        public string $archiveImageBase64,
        public ?AdjustmentMetricsData $metrics,
        public ?bool $acceptedWithoutChanges,
        public ?bool $rotationWasCorrect,
    ) {}

    public function handle(AnnotationRepositoryInterface $repository): void
    {
        $annotation = $repository->findOrFail($this->annotationId);

        // Update status to processing
        $repository->update($this->annotationId, [
            'status' => AnnotationStatus::Processing,
        ]);

        // Update with final data
        $annotation = $repository->update($this->annotationId, [
            'status' => AnnotationStatus::Processed,
            'final_corners' => $this->finalCorners->toArray(),
            'final_rotation' => $this->finalRotation,
            'annotated_by' => $this->annotatedBy,
            'annotated_at' => now(),
            'time_spent_seconds' => $this->timeSpentSeconds,
            'accepted_without_changes' => $this->acceptedWithoutChanges,
            'corner_adjustments' => $this->metrics?->toArray(),
            'rotation_was_correct' => $this->rotationWasCorrect,
        ]);

        // Dispatch event with image data for consuming app
        event(new AnnotationCompleted(
            annotation: $annotation,
            displayImageBase64: $this->displayImageBase64,
            archiveImageBase64: $this->archiveImageBase64,
        ));
    }
}
