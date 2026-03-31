<?php

declare(strict_types=1);

use RobinsonRyan\FourCorners\Data\CornersData;
use RobinsonRyan\FourCorners\Data\PointData;
use RobinsonRyan\FourCorners\Services\MetricsCalculator;

it('calculates adjustment distances between suggested and final corners', function (): void {
    $calculator = new MetricsCalculator;

    $suggested = new CornersData(
        top_left: new PointData(x: 100, y: 100),
        top_right: new PointData(x: 900, y: 100),
        bottom_right: new PointData(x: 900, y: 600),
        bottom_left: new PointData(x: 100, y: 600),
    );

    $final = new CornersData(
        top_left: new PointData(x: 103, y: 104),
        top_right: new PointData(x: 900, y: 100),
        bottom_right: new PointData(x: 900, y: 600),
        bottom_left: new PointData(x: 100, y: 600),
    );

    $metrics = $calculator->calculateAdjustments($suggested, $final);

    expect($metrics->top_left)->toBe(5.0)
        ->and($metrics->top_right)->toBe(0.0)
        ->and($metrics->bottom_right)->toBe(0.0)
        ->and($metrics->bottom_left)->toBe(0.0)
        ->and($metrics->total)->toBe(5.0)
        ->and($metrics->mean)->toBe(1.25);
});

it('returns zero metrics when corners are identical', function (): void {
    $calculator = new MetricsCalculator;

    $corners = new CornersData(
        top_left: new PointData(x: 100, y: 100),
        top_right: new PointData(x: 900, y: 100),
        bottom_right: new PointData(x: 900, y: 600),
        bottom_left: new PointData(x: 100, y: 600),
    );

    $metrics = $calculator->calculateAdjustments($corners, $corners);

    expect($metrics->total)->toBe(0.0)
        ->and($metrics->mean)->toBe(0.0)
        ->and($metrics->wasAcceptedWithoutChanges())->toBeTrue();
});

it('calculates diagonal movement correctly', function (): void {
    $calculator = new MetricsCalculator;

    $suggested = new CornersData(
        top_left: new PointData(x: 0, y: 0),
        top_right: new PointData(x: 100, y: 0),
        bottom_right: new PointData(x: 100, y: 100),
        bottom_left: new PointData(x: 0, y: 100),
    );

    // Move each corner by 3,4 (distance = 5)
    $final = new CornersData(
        top_left: new PointData(x: 3, y: 4),
        top_right: new PointData(x: 103, y: 4),
        bottom_right: new PointData(x: 103, y: 104),
        bottom_left: new PointData(x: 3, y: 104),
    );

    $metrics = $calculator->calculateAdjustments($suggested, $final);

    expect($metrics->top_left)->toBe(5.0)
        ->and($metrics->top_right)->toBe(5.0)
        ->and($metrics->bottom_right)->toBe(5.0)
        ->and($metrics->bottom_left)->toBe(5.0)
        ->and($metrics->total)->toBe(20.0)
        ->and($metrics->mean)->toBe(5.0);
});
