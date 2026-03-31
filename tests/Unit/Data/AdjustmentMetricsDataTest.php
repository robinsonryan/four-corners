<?php

declare(strict_types=1);

use RobinsonRyan\FourCorners\Data\AdjustmentMetricsData;

it('can create adjustment metrics', function (): void {
    $metrics = new AdjustmentMetricsData(
        top_left: 2.5,
        top_right: 1.5,
        bottom_right: 3.0,
        bottom_left: 2.0,
        total: 9.0,
        mean: 2.25,
    );

    expect($metrics->top_left)->toBe(2.5)
        ->and($metrics->total)->toBe(9.0)
        ->and($metrics->mean)->toBe(2.25);
});

it('determines if corners were accepted without changes when mean is under 2px', function (): void {
    $metricsAccepted = new AdjustmentMetricsData(
        top_left: 0.5,
        top_right: 0.5,
        bottom_right: 0.5,
        bottom_left: 0.5,
        total: 2.0,
        mean: 0.5,
    );

    $metricsAdjusted = new AdjustmentMetricsData(
        top_left: 5.0,
        top_right: 5.0,
        bottom_right: 5.0,
        bottom_left: 5.0,
        total: 20.0,
        mean: 5.0,
    );

    expect($metricsAccepted->wasAcceptedWithoutChanges())->toBeTrue()
        ->and($metricsAdjusted->wasAcceptedWithoutChanges())->toBeFalse();
});

it('can be created from array', function (): void {
    $metrics = AdjustmentMetricsData::from([
        'top_left' => 2.5,
        'top_right' => 1.5,
        'bottom_right' => 3.0,
        'bottom_left' => 2.0,
        'total' => 9.0,
        'mean' => 2.25,
    ]);

    expect($metrics->mean)->toBe(2.25);
});
