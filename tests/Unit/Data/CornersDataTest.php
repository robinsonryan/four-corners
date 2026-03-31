<?php

declare(strict_types=1);

use RobinsonRyan\FourCorners\Data\CornersData;
use RobinsonRyan\FourCorners\Data\PointData;

it('can create corners from point data objects', function (): void {
    $corners = new CornersData(
        top_left: new PointData(x: 100, y: 100),
        top_right: new PointData(x: 900, y: 100),
        bottom_right: new PointData(x: 900, y: 600),
        bottom_left: new PointData(x: 100, y: 600),
    );

    expect($corners->top_left->x)->toBe(100.0)
        ->and($corners->top_right->x)->toBe(900.0)
        ->and($corners->bottom_right->y)->toBe(600.0)
        ->and($corners->bottom_left->y)->toBe(600.0);
});

it('can be created from nested array', function (): void {
    $corners = CornersData::from([
        'top_left' => ['x' => 100, 'y' => 100],
        'top_right' => ['x' => 900, 'y' => 100],
        'bottom_right' => ['x' => 900, 'y' => 600],
        'bottom_left' => ['x' => 100, 'y' => 600],
    ]);

    expect($corners->top_left)->toBeInstanceOf(PointData::class)
        ->and($corners->top_left->x)->toBe(100.0);
});

it('validates corners are within image bounds', function (): void {
    $corners = new CornersData(
        top_left: new PointData(x: 100, y: 100),
        top_right: new PointData(x: 900, y: 100),
        bottom_right: new PointData(x: 900, y: 600),
        bottom_left: new PointData(x: 100, y: 600),
    );

    expect($corners->isWithinBounds(1000, 700))->toBeTrue()
        ->and($corners->isWithinBounds(800, 500))->toBeFalse();
});

it('validates corners form a convex quadrilateral', function (): void {
    $convexCorners = new CornersData(
        top_left: new PointData(x: 100, y: 100),
        top_right: new PointData(x: 900, y: 100),
        bottom_right: new PointData(x: 900, y: 600),
        bottom_left: new PointData(x: 100, y: 600),
    );

    expect($convexCorners->isConvex())->toBeTrue();
});

it('detects non-convex quadrilaterals', function (): void {
    // Create a "bowtie" shape (non-convex)
    $nonConvexCorners = new CornersData(
        top_left: new PointData(x: 100, y: 100),
        top_right: new PointData(x: 100, y: 600),
        bottom_right: new PointData(x: 900, y: 100),
        bottom_left: new PointData(x: 900, y: 600),
    );

    expect($nonConvexCorners->isConvex())->toBeFalse();
});

it('can be converted to array', function (): void {
    $corners = new CornersData(
        top_left: new PointData(x: 100, y: 100),
        top_right: new PointData(x: 900, y: 100),
        bottom_right: new PointData(x: 900, y: 600),
        bottom_left: new PointData(x: 100, y: 600),
    );

    $array = $corners->toArray();

    expect($array)->toHaveKeys(['top_left', 'top_right', 'bottom_right', 'bottom_left'])
        ->and($array['top_left'])->toBe(['x' => 100.0, 'y' => 100.0]);
});
