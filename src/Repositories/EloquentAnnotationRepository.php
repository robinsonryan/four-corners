<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Repositories;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RobinsonRyan\FourCorners\Contracts\AnnotationRepositoryInterface;
use RobinsonRyan\FourCorners\Enums\AnnotationStatus;
use RobinsonRyan\FourCorners\Exceptions\AnnotationNotFoundException;
use RobinsonRyan\FourCorners\Models\DocumentAnnotation;

final class EloquentAnnotationRepository implements AnnotationRepositoryInterface
{
    public function create(array $data): DocumentAnnotation
    {
        return DocumentAnnotation::create($data);
    }

    public function find(string $id): ?DocumentAnnotation
    {
        return DocumentAnnotation::find($id);
    }

    public function findOrFail(string $id): DocumentAnnotation
    {
        $annotation = $this->find($id);

        if ($annotation === null) {
            throw AnnotationNotFoundException::withId($id);
        }

        return $annotation;
    }

    public function update(string $id, array $data): DocumentAnnotation
    {
        $annotation = $this->findOrFail($id);
        $annotation->update($data);

        /** @var DocumentAnnotation */
        return $annotation->fresh();
    }

    public function getPendingByType(string|int $documentTypeId, int $limit = 50): Collection
    {
        return DocumentAnnotation::query()
            ->pending()
            ->forDocumentType($documentTypeId)
            ->orderBy('created_at')
            ->limit($limit)
            ->get();
    }

    public function getMetricsSummary(
        ?Carbon $from = null,
        ?Carbon $to = null,
        string|int|null $documentTypeId = null,
    ): array {
        $query = DocumentAnnotation::query()
            ->where('status', AnnotationStatus::Processed);

        if ($from !== null) {
            $query->where('annotated_at', '>=', $from);
        }

        if ($to !== null) {
            $query->where('annotated_at', '<=', $to);
        }

        if ($documentTypeId !== null) {
            $query->forDocumentType($documentTypeId);
        }

        $total = $query->count();
        $acceptedWithoutChanges = (clone $query)->where('accepted_without_changes', true)->count();
        $rotationCorrect = (clone $query)->where('rotation_was_correct', true)->count();

        // Calculate average adjustment using raw query for JSON field
        $avgAdjustment = (clone $query)
            ->whereNotNull('corner_adjustments')
            ->select(DB::raw('AVG(JSON_EXTRACT(corner_adjustments, "$.mean")) as avg_mean'))
            ->value('avg_mean');

        $rejectedQuery = DocumentAnnotation::query()
            ->where('status', AnnotationStatus::Rejected)
            ->when($from, fn ($q) => $q->where('annotated_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('annotated_at', '<=', $to));

        if ($documentTypeId !== null) {
            $rejectedQuery->forDocumentType($documentTypeId);
        }

        $rejectedCount = $rejectedQuery->count();

        return [
            'total_processed' => $total,
            'total_rejected' => $rejectedCount,
            'accepted_without_changes' => $acceptedWithoutChanges,
            'accepted_without_changes_rate' => $total > 0 ? round($acceptedWithoutChanges / $total * 100, 2) : 0,
            'rotation_correct' => $rotationCorrect,
            'rotation_correct_rate' => $total > 0 ? round($rotationCorrect / $total * 100, 2) : 0,
            'avg_corner_adjustment_px' => $avgAdjustment !== null ? round((float) $avgAdjustment, 2) : null,
        ];
    }
}
