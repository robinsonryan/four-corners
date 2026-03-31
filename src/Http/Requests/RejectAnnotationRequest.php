<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RejectAnnotationRequest extends FormRequest
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
            'rejection_reason_id' => ['required', 'exists:'.config('four_corners.table_prefix', '').'rejection_reasons,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'time_spent_seconds' => ['required', 'numeric', 'min:0'],
        ];
    }
}
