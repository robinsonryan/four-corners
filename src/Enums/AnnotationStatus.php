<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Enums;

enum AnnotationStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Processed = 'processed';
    case Rejected = 'rejected';
}
