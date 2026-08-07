<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RobinsonRyan\FourCorners\Models\DocumentAnnotation;

/**
 * @mixin DocumentAnnotation
 */
final class AnnotationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_image_path' => $this->original_image_path,
            'original_width' => $this->original_width,
            'original_height' => $this->original_height,
            'document_type_id' => $this->document_type_id,
            'status' => $this->status->value,
            'auto_detection' => $this->auto_detection,
            'final_corners' => $this->final_corners,
            'final_rotation' => $this->final_rotation,
            'accepted_without_changes' => $this->accepted_without_changes,
            'corner_adjustments' => $this->corner_adjustments,
            'rotation_was_correct' => $this->rotation_was_correct,
            'display_image_path' => $this->display_image_path,
            'archive_image_path' => $this->archive_image_path,
            'rejection_reason_id' => $this->rejection_reason_id,
            'rejection_notes' => $this->rejection_notes,
            'annotated_by' => $this->annotated_by,
            'annotated_at' => $this->annotated_at?->toIso8601String(),
            'time_spent_seconds' => $this->time_spent_seconds,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'document_type' => $this->whenLoaded('documentType', fn (): DocumentTypeResource => new DocumentTypeResource($this->documentType)),
            'rejection_reason' => $this->whenLoaded('rejectionReason', fn (): RejectionReasonResource => new RejectionReasonResource($this->rejectionReason)),
        ];
    }
}
