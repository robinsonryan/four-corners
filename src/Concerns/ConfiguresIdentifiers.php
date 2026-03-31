<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Concerns;

use Illuminate\Support\Str;

/**
 * Configures model primary key settings based on package configuration.
 */
trait ConfiguresIdentifiers
{
    public function getIncrementing(): bool
    {
        return config('four_corners.id_type') !== 'uuid7';
    }

    public function getKeyType(): string
    {
        return config('four_corners.id_type') === 'uuid7' ? 'string' : 'int';
    }

    protected static function bootConfiguresIdentifiers(): void
    {
        if (config('four_corners.id_type') === 'uuid7') {
            static::creating(function ($model): void {
                if (empty($model->{$model->getKeyName()})) {
                    $model->{$model->getKeyName()} = Str::uuid7()->toString();
                }
            });
        }
    }
}
