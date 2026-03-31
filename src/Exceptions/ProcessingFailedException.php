<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Exceptions;

use Exception;

final class ProcessingFailedException extends Exception
{
    public static function forAnnotation(string $id, string $reason): self
    {
        return new self("Failed to process annotation [{$id}]: {$reason}");
    }
}
