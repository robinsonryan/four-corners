<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Contracts;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RobinsonRyan\FourCorners\Models\DocumentAnnotation;

interface AnnotationRepositoryInterface
{
    /**
     * Create a new annotation.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): DocumentAnnotation;

    /**
     * Find an annotation by ID.
     */
    public function find(string $id): ?DocumentAnnotation;

    /**
     * Find an annotation by ID or throw exception.
     *
     * @throws \RobinsonRyan\FourCorners\Exceptions\AnnotationNotFoundException
     */
    public function findOrFail(string $id): DocumentAnnotation;

    /**
     * Update an annotation.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(string $id, array $data): DocumentAnnotation;

    /**
     * Get pending annotations by document type.
     *
     * @return Collection<int, DocumentAnnotation>
     */
    public function getPendingByType(string|int $documentTypeId, int $limit = 50): Collection;

    /**
     * Get metrics summary for reporting.
     *
     * @return array<string, mixed>
     */
    public function getMetricsSummary(
        ?Carbon $from = null,
        ?Carbon $to = null,
        string|int|null $documentTypeId = null,
    ): array;
}
