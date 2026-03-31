<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RobinsonRyan\FourCorners\Data\AdjustmentMetricsData;
use RobinsonRyan\FourCorners\Data\CornersData;
use RobinsonRyan\FourCorners\Models\DocumentAnnotation;

final class CornersAdjusted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public DocumentAnnotation $annotation,
        public CornersData $suggestedCorners,
        public CornersData $finalCorners,
        public AdjustmentMetricsData $adjustments,
    ) {}
}
