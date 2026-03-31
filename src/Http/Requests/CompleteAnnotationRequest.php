<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CompleteAnnotationRequest extends FormRequest
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
            'final_corners' => ['required', 'array'],
            'final_corners.top_left' => ['required', 'array'],
            'final_corners.top_left.x' => ['required', 'numeric', 'min:0'],
            'final_corners.top_left.y' => ['required', 'numeric', 'min:0'],
            'final_corners.top_right' => ['required', 'array'],
            'final_corners.top_right.x' => ['required', 'numeric', 'min:0'],
            'final_corners.top_right.y' => ['required', 'numeric', 'min:0'],
            'final_corners.bottom_right' => ['required', 'array'],
            'final_corners.bottom_right.x' => ['required', 'numeric', 'min:0'],
            'final_corners.bottom_right.y' => ['required', 'numeric', 'min:0'],
            'final_corners.bottom_left' => ['required', 'array'],
            'final_corners.bottom_left.x' => ['required', 'numeric', 'min:0'],
            'final_corners.bottom_left.y' => ['required', 'numeric', 'min:0'],
            'final_rotation' => ['required', 'integer', 'in:0,90,180,270'],
            'time_spent_seconds' => ['required', 'numeric', 'min:0'],
            'display_image' => ['required', 'string'],
            'archive_image' => ['required', 'string'],
        ];
    }
}
