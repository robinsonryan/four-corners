<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Contracts;

/**
 * Interface for server-side image processing (future ML integration).
 */
interface ImageProcessorInterface
{
    /**
     * Process an image for corner detection (future ML integration point).
     *
     * @return array{corners: array<string, array{x: float, y: float}>|null, rotation: int, confidence: float, method: string, detection_time_ms: int}
     */
    public function detectCorners(string $imagePath): array;
}
