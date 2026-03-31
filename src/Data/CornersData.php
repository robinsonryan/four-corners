<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Data;

use Spatie\LaravelData\Data;

final class CornersData extends Data
{
    public function __construct(
        public PointData $top_left,
        public PointData $top_right,
        public PointData $bottom_right,
        public PointData $bottom_left,
    ) {}

    /**
     * Check if all corners are within the given bounds.
     */
    public function isWithinBounds(int $width, int $height): bool
    {
        foreach ([$this->top_left, $this->top_right, $this->bottom_right, $this->bottom_left] as $point) {
            if ($point->x < 0 || $point->x > $width || $point->y < 0 || $point->y > $height) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the quadrilateral forms a valid convex shape.
     */
    public function isConvex(): bool
    {
        $points = [
            [$this->top_left->x, $this->top_left->y],
            [$this->top_right->x, $this->top_right->y],
            [$this->bottom_right->x, $this->bottom_right->y],
            [$this->bottom_left->x, $this->bottom_left->y],
        ];

        $n = count($points);
        $sign = null;

        for ($i = 0; $i < $n; $i++) {
            $dx1 = $points[($i + 2) % $n][0] - $points[($i + 1) % $n][0];
            $dy1 = $points[($i + 2) % $n][1] - $points[($i + 1) % $n][1];
            $dx2 = $points[$i][0] - $points[($i + 1) % $n][0];
            $dy2 = $points[$i][1] - $points[($i + 1) % $n][1];

            $cross = $dx1 * $dy2 - $dy1 * $dx2;

            if ($cross !== 0.0) {
                $currentSign = $cross > 0;
                if ($sign === null) {
                    $sign = $currentSign;
                } elseif ($sign !== $currentSign) {
                    return false;
                }
            }
        }

        return true;
    }
}
