<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Services;

use RobinsonRyan\FourCorners\Data\AdjustmentMetricsData;
use RobinsonRyan\FourCorners\Data\CornersData;

final class MetricsCalculator
{
    /**
     * Calculate adjustment distances between suggested and final corners.
     */
    public function calculateAdjustments(
        CornersData $suggested,
        CornersData $final,
    ): AdjustmentMetricsData {
        $topLeft = $suggested->top_left->distanceTo($final->top_left);
        $topRight = $suggested->top_right->distanceTo($final->top_right);
        $bottomRight = $suggested->bottom_right->distanceTo($final->bottom_right);
        $bottomLeft = $suggested->bottom_left->distanceTo($final->bottom_left);

        $total = $topLeft + $topRight + $bottomRight + $bottomLeft;
        $mean = $total / 4;

        return new AdjustmentMetricsData(
            top_left: round($topLeft, 2),
            top_right: round($topRight, 2),
            bottom_right: round($bottomRight, 2),
            bottom_left: round($bottomLeft, 2),
            total: round($total, 2),
            mean: round($mean, 2),
        );
    }
}
