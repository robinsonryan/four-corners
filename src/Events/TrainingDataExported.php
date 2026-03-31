<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TrainingDataExported
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public string $path,
        public int $count,
        public string $format,
    ) {}
}
