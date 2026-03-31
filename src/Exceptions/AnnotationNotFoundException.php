<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Exceptions;

use Exception;

final class AnnotationNotFoundException extends Exception
{
    public static function withId(string $id): self
    {
        return new self("Annotation with ID [{$id}] not found.");
    }
}
