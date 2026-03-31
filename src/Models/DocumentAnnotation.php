<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use RobinsonRyan\FourCorners\Concerns\ConfiguresIdentifiers;
use RobinsonRyan\FourCorners\Enums\AnnotationStatus;
use RobinsonRyan\FourCorners\Support\TablePrefixer;

/**
 * @property string|int $id
 * @property string $original_image_path
 * @property int $original_width
 * @property int $original_height
 * @property string|int $document_type_id
 * @property AnnotationStatus $status
 * @property array<string, mixed>|null $auto_detection
 * @property array<string, mixed>|null $final_corners
 * @property int $final_rotation
 * @property bool|null $accepted_without_changes
 * @property array<string, mixed>|null $corner_adjustments
 * @property bool|null $rotation_was_correct
 * @property string|null $display_image_path
 * @property string|null $archive_image_path
 * @property string|int|null $rejection_reason_id
 * @property string|null $rejection_notes
 * @property int|null $annotated_by
 * @property Carbon|null $annotated_at
 * @property float|null $time_spent_seconds
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DocumentType $documentType
 * @property-read RejectionReason|null $rejectionReason
 */
final class DocumentAnnotation extends Model
{
    use ConfiguresIdentifiers;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'original_width' => 'integer',
            'original_height' => 'integer',
            'status' => AnnotationStatus::class,
            'auto_detection' => 'array',
            'final_corners' => 'array',
            'final_rotation' => 'integer',
            'accepted_without_changes' => 'boolean',
            'corner_adjustments' => 'array',
            'rotation_was_correct' => 'boolean',
            'annotated_by' => 'integer',
            'annotated_at' => 'datetime',
            'time_spent_seconds' => 'float',
        ];
    }

    public function getTable(): string
    {
        return TablePrefixer::prefix('document_annotations');
    }

    /**
     * @return BelongsTo<DocumentType, $this>
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * @return BelongsTo<RejectionReason, $this>
     */
    public function rejectionReason(): BelongsTo
    {
        return $this->belongsTo(RejectionReason::class);
    }

    /**
     * @param  Builder<DocumentAnnotation>  $query
     * @return Builder<DocumentAnnotation>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', AnnotationStatus::Pending);
    }

    /**
     * @param  Builder<DocumentAnnotation>  $query
     * @return Builder<DocumentAnnotation>
     */
    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', AnnotationStatus::Processing);
    }

    /**
     * @param  Builder<DocumentAnnotation>  $query
     * @return Builder<DocumentAnnotation>
     */
    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('status', AnnotationStatus::Processed);
    }

    /**
     * @param  Builder<DocumentAnnotation>  $query
     * @return Builder<DocumentAnnotation>
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', AnnotationStatus::Rejected);
    }

    /**
     * @param  Builder<DocumentAnnotation>  $query
     * @return Builder<DocumentAnnotation>
     */
    public function scopeForDocumentType(Builder $query, string|int $documentTypeId): Builder
    {
        return $query->where('document_type_id', $documentTypeId);
    }
}
