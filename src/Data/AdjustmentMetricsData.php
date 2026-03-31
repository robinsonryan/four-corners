<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Data;

use Spatie\LaravelData\Data;

final class AdjustmentMetricsData extends Data
{
    public function __construct(
        public float $top_left,
        public float $top_right,
        public float $bottom_right,
        public float $bottom_left,
        public float $total,
        public float $mean,
    ) {}

    /**
     * Check if the corners were accepted without significant changes.
     * Less than 2px average adjustment is considered "accepted without changes".
     */
    public function wasAcceptedWithoutChanges(): bool
    {
        return $this->mean < 2.0;
    }
}
