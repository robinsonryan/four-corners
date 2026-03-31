<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Data;

use Spatie\LaravelData\Data;

final class RejectionData extends Data
{
    public function __construct(
        public int $rejection_reason_id,
        public ?string $notes,
        public int $rejected_by,
        public float $time_spent_seconds,
    ) {}
}
