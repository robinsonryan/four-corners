<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Support;

final class TablePrefixer
{
    public static function prefix(string $table): string
    {
        $prefix = config('four_corners.table_prefix', '');

        if ($prefix === null || $prefix === '') {
            return $table;
        }

        return $prefix.$table;
    }
}
