<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RobinsonRyan\FourCorners\Models\DocumentAnnotation;

final class AnnotationStarted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public DocumentAnnotation $annotation,
    ) {}
}
