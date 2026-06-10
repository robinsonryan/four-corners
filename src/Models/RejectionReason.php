<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;
use RobinsonRyan\FourCorners\Concerns\ConfiguresIdentifiers;
use RobinsonRyan\FourCorners\Support\TablePrefixer;

/**
 * @property string|int $id
 * @property string $code
 * @property string $label
 * @property string|null $description
 * @property int $sort_order
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class RejectionReason extends Model
{
    use ConfiguresIdentifiers;

    protected $guarded = [];

    #[Override]
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    #[Override]
    public function getTable(): string
    {
        return TablePrefixer::prefix('rejection_reasons');
    }

    /**
     * @return HasMany<DocumentAnnotation, $this>
     */
    public function annotations(): HasMany
    {
        return $this->hasMany(DocumentAnnotation::class);
    }

    /**
     * @param  Builder<RejectionReason>  $query
     * @return Builder<RejectionReason>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<RejectionReason>  $query
     * @return Builder<RejectionReason>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}
