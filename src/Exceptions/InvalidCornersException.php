<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Exceptions;

use Exception;

final class InvalidCornersException extends Exception
{
    public static function notConvex(): self
    {
        return new self('Corners do not form a convex quadrilateral.');
    }

    public static function outOfBounds(int $width, int $height): self
    {
        return new self("Corners are outside image bounds ({$width}x{$height}).");
    }
}
