<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;
use RobinsonRyan\FourCorners\Models\RejectionReason;

/**
 * @mixin RejectionReason
 */
final class RejectionReasonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'label' => $this->label,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];
    }
}
