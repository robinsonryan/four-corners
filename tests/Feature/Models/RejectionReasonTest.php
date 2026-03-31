<?php

declare(strict_types=1);

use RobinsonRyan\FourCorners\Models\RejectionReason;

it('can create a rejection reason', function (): void {
    $reason = RejectionReason::create([
        'code' => 'too_blurry',
        'label' => 'Too Blurry',
        'description' => 'Motion blur or out of focus',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    expect($reason->exists)->toBeTrue()
        ->and($reason->code)->toBe('too_blurry')
        ->and($reason->label)->toBe('Too Blurry')
        ->and($reason->fresh()->is_active)->toBeTrue();
});

it('scopes to active rejection reasons', function (): void {
    RejectionReason::create([
        'code' => 'active_reason',
        'label' => 'Active Reason',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    RejectionReason::create([
        'code' => 'inactive_reason',
        'label' => 'Inactive Reason',
        'sort_order' => 2,
        'is_active' => false,
    ]);

    expect(RejectionReason::active()->count())->toBe(1)
        ->and(RejectionReason::active()->first()->code)->toBe('active_reason');
});

it('orders by sort_order', function (): void {
    RejectionReason::create([
        'code' => 'second',
        'label' => 'Second',
        'sort_order' => 2,
    ]);

    RejectionReason::create([
        'code' => 'first',
        'label' => 'First',
        'sort_order' => 1,
    ]);

    $ordered = RejectionReason::ordered()->get();

    expect($ordered->first()->code)->toBe('first')
        ->and($ordered->last()->code)->toBe('second');
});
