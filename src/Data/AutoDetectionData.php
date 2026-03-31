<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Data;

use RobinsonRyan\FourCorners\Enums\DetectionMethod;
use Spatie\LaravelData\Data;

final class AutoDetectionData extends Data
{
    public function __construct(
        public DetectionMethod $method,
        public ?CornersData $corners,
        public int $rotation_suggestion,
        public float $confidence,
        public int $detection_time_ms,
    ) {}
}
