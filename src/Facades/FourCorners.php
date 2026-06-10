<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Facades;

use Illuminate\Support\Facades\Facade;
use RobinsonRyan\FourCorners\Services\AnnotationService;

/**
 * @method static \RobinsonRyan\FourCorners\Models\DocumentAnnotation start(string $originalImagePath, int $originalWidth, int $originalHeight, string|int $documentTypeId, ?\RobinsonRyan\FourCorners\Data\AutoDetectionData $autoDetection = null)
 * @method static \RobinsonRyan\FourCorners\Models\DocumentAnnotation complete(string $annotationId, \RobinsonRyan\FourCorners\Data\CornersData $finalCorners, int $finalRotation, int|string $annotatedBy, float $timeSpentSeconds, string $displayImageBase64, string $archiveImageBase64)
 * @method static \RobinsonRyan\FourCorners\Models\DocumentAnnotation reject(string $annotationId, string|int $rejectionReasonId, ?string $notes, int|string $rejectedBy, float $timeSpentSeconds)
 * @method static \RobinsonRyan\FourCorners\Models\DocumentAnnotation|null find(string $id)
 * @method static \Illuminate\Support\Collection<int, \RobinsonRyan\FourCorners\Models\DocumentAnnotation> getPending(string|int $documentTypeId, int $limit = 50)
 * @method static array<string, mixed> getMetricsSummary(?\Illuminate\Support\Carbon $from = null, ?\Illuminate\Support\Carbon $to = null, string|int|null $documentTypeId = null)
 *
 * @see AnnotationService
 */
final class FourCorners extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AnnotationService::class;
    }
}
