<?php

declare(strict_types=1);

use RobinsonRyan\FourCorners\Data\PointData;

it('can create a point with coordinates', function (): void {
    $point = new PointData(x: 100.5, y: 200.5);

    expect($point->x)->toBe(100.5)
        ->and($point->y)->toBe(200.5);
});

it('can calculate distance to another point', function (): void {
    $point1 = new PointData(x: 0, y: 0);
    $point2 = new PointData(x: 3, y: 4);

    expect($point1->distanceTo($point2))->toBe(5.0);
});

it('can be created from array', function (): void {
    $point = PointData::from(['x' => 50, 'y' => 75]);

    expect($point->x)->toBe(50.0)
        ->and($point->y)->toBe(75.0);
});

it('can be converted to array', function (): void {
    $point = new PointData(x: 100, y: 200);

    expect($point->toArray())->toBe([
        'x' => 100.0,
        'y' => 200.0,
    ]);
});
