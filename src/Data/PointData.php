<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Data;

use Spatie\LaravelData\Data;

final class PointData extends Data
{
    public function __construct(
        public float $x,
        public float $y,
    ) {}

    public function distanceTo(PointData $other): float
    {
        return sqrt(($other->x - $this->x) ** 2 + ($other->y - $this->y) ** 2);
    }
}
