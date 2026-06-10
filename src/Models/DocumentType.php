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
 * @property string $name
 * @property float $aspect_ratio_width
 * @property float $aspect_ratio_height
 * @property int $display_width
 * @property int $display_height
 * @property int $archive_width
 * @property int $archive_height
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class DocumentType extends Model
{
    use ConfiguresIdentifiers;

    protected $guarded = [];

    #[Override]
    protected function casts(): array
    {
        return [
            'aspect_ratio_width' => 'float',
            'aspect_ratio_height' => 'float',
            'display_width' => 'integer',
            'display_height' => 'integer',
            'archive_width' => 'integer',
            'archive_height' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    #[Override]
    public function getTable(): string
    {
        return TablePrefixer::prefix('document_types');
    }

    /**
     * @return HasMany<DocumentAnnotation, $this>
     */
    public function annotations(): HasMany
    {
        return $this->hasMany(DocumentAnnotation::class);
    }

    /**
     * @param  Builder<DocumentType>  $query
     * @return Builder<DocumentType>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
