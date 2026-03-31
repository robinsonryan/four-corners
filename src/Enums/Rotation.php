<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Enums;

enum Rotation: int
{
    case None = 0;
    case Clockwise90 = 90;
    case Rotate180 = 180;
    case CounterClockwise90 = 270;
}
