<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;
use RobinsonRyan\FourCorners\Models\DocumentType;

/**
 * @mixin DocumentType
 */
final class DocumentTypeResource extends JsonResource
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
            'name' => $this->name,
            'aspect_ratio_width' => $this->aspect_ratio_width,
            'aspect_ratio_height' => $this->aspect_ratio_height,
            'display_width' => $this->display_width,
            'display_height' => $this->display_height,
            'archive_width' => $this->archive_width,
            'archive_height' => $this->archive_height,
            'is_active' => $this->is_active,
        ];
    }
}
