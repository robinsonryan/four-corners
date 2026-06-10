<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RobinsonRyan\FourCorners\Contracts\AnnotationRepositoryInterface;
use RobinsonRyan\FourCorners\Data\AutoDetectionData;
use RobinsonRyan\FourCorners\Data\CornersData;
use RobinsonRyan\FourCorners\Enums\AnnotationStatus;
use RobinsonRyan\FourCorners\Events\AnnotationRejected;
use RobinsonRyan\FourCorners\Events\AnnotationStarted;
use RobinsonRyan\FourCorners\Events\CornersAdjusted;
use RobinsonRyan\FourCorners\Jobs\ProcessAnnotationJob;
use RobinsonRyan\FourCorners\Models\DocumentAnnotation;

final readonly class AnnotationService
{
    public function __construct(
        private AnnotationRepositoryInterface $repository,
        private MetricsCalculator $metricsCalculator,
    ) {}

    /**
     * Start a new annotation session.
     */
    public function start(
        string $originalImagePath,
        int $originalWidth,
        int $originalHeight,
        string|int $documentTypeId,
        ?AutoDetectionData $autoDetection = null,
    ): DocumentAnnotation {
        $annotation = $this->repository->create([
            'original_image_path' => $originalImagePath,
            'original_width' => $originalWidth,
            'original_height' => $originalHeight,
            'document_type_id' => $documentTypeId,
            'status' => AnnotationStatus::Pending,
            'auto_detection' => $autoDetection?->toArray(),
        ]);

        event(new AnnotationStarted($annotation));

        return $annotation;
    }

    /**
     * Complete an annotation with final corners and rotation.
     */
    public function complete(
        string $annotationId,
        CornersData $finalCorners,
        int $finalRotation,
        int $annotatedBy,
        float $timeSpentSeconds,
        string $displayImageBase64,
        string $archiveImageBase64,
    ): DocumentAnnotation {
        $annotation = $this->repository->findOrFail($annotationId);

        // Calculate metrics if auto-detection was performed
        $metrics = null;
        $acceptedWithoutChanges = null;
        $rotationWasCorrect = null;

        if ($annotation->auto_detection !== null) {
            $autoDetection = AutoDetectionData::from($annotation->auto_detection);

            if ($autoDetection->corners instanceof CornersData) {
                $metrics = $this->metricsCalculator->calculateAdjustments(
                    $autoDetection->corners,
                    $finalCorners,
                );
                $acceptedWithoutChanges = $metrics->wasAcceptedWithoutChanges();

                event(new CornersAdjusted($annotation, $autoDetection->corners, $finalCorners, $metrics));
            }

            $rotationWasCorrect = $autoDetection->rotation_suggestion === $finalRotation;
        }

        // Dispatch job to handle storage
        ProcessAnnotationJob::dispatch(
            $annotationId,
            $finalCorners,
            $finalRotation,
            $annotatedBy,
            $timeSpentSeconds,
            $displayImageBase64,
            $archiveImageBase64,
            $metrics,
            $acceptedWithoutChanges,
            $rotationWasCorrect,
        )->onQueue(config('four_corners.queue.queue_name', 'annotations'));

        /** @var DocumentAnnotation */
        return $annotation->fresh();
    }

    /**
     * Reject an annotation.
     */
    public function reject(
        string $annotationId,
        string|int $rejectionReasonId,
        ?string $notes,
        int $rejectedBy,
        float $timeSpentSeconds,
    ): DocumentAnnotation {
        $annotation = $this->repository->update($annotationId, [
            'status' => AnnotationStatus::Rejected,
            'rejection_reason_id' => $rejectionReasonId,
            'rejection_notes' => $notes,
            'annotated_by' => $rejectedBy,
            'annotated_at' => now(),
            'time_spent_seconds' => $timeSpentSeconds,
        ]);

        event(new AnnotationRejected($annotation));

        return $annotation;
    }

    /**
     * Find an annotation by ID.
     */
    public function find(string $id): ?DocumentAnnotation
    {
        return $this->repository->find($id);
    }

    /**
     * Get pending annotations for a document type.
     *
     * @return Collection<int, DocumentAnnotation>
     */
    public function getPending(string|int $documentTypeId, int $limit = 50): Collection
    {
        return $this->repository->getPendingByType($documentTypeId, $limit);
    }

    /**
     * Get metrics summary for reporting.
     *
     * @return array<string, mixed>
     */
    public function getMetricsSummary(
        ?Carbon $from = null,
        ?Carbon $to = null,
        string|int|null $documentTypeId = null,
    ): array {
        return $this->repository->getMetricsSummary($from, $to, $documentTypeId);
    }
}
