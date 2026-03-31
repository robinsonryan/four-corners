<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StartAnnotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'original_image_path' => ['required', 'string', 'max:500'],
            'original_width' => ['required', 'integer', 'min:1'],
            'original_height' => ['required', 'integer', 'min:1'],
            'document_type_id' => ['required', 'exists:'.config('four_corners.table_prefix', '').'document_types,id'],
            'auto_detection' => ['nullable', 'array'],
            'auto_detection.method' => ['required_with:auto_detection', 'string'],
            'auto_detection.corners' => ['nullable', 'array'],
            'auto_detection.corners.top_left' => ['required_with:auto_detection.corners', 'array'],
            'auto_detection.corners.top_left.x' => ['required_with:auto_detection.corners', 'numeric'],
            'auto_detection.corners.top_left.y' => ['required_with:auto_detection.corners', 'numeric'],
            'auto_detection.corners.top_right' => ['required_with:auto_detection.corners', 'array'],
            'auto_detection.corners.top_right.x' => ['required_with:auto_detection.corners', 'numeric'],
            'auto_detection.corners.top_right.y' => ['required_with:auto_detection.corners', 'numeric'],
            'auto_detection.corners.bottom_right' => ['required_with:auto_detection.corners', 'array'],
            'auto_detection.corners.bottom_right.x' => ['required_with:auto_detection.corners', 'numeric'],
            'auto_detection.corners.bottom_right.y' => ['required_with:auto_detection.corners', 'numeric'],
            'auto_detection.corners.bottom_left' => ['required_with:auto_detection.corners', 'array'],
            'auto_detection.corners.bottom_left.x' => ['required_with:auto_detection.corners', 'numeric'],
            'auto_detection.corners.bottom_left.y' => ['required_with:auto_detection.corners', 'numeric'],
            'auto_detection.rotation_suggestion' => ['required_with:auto_detection', 'integer', 'in:0,90,180,270'],
            'auto_detection.confidence' => ['required_with:auto_detection', 'numeric', 'min:0', 'max:1'],
            'auto_detection.detection_time_ms' => ['required_with:auto_detection', 'integer', 'min:0'],
        ];
    }
}
