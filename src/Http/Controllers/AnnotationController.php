<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use RobinsonRyan\FourCorners\Data\AutoDetectionData;
use RobinsonRyan\FourCorners\Data\CornersData;
use RobinsonRyan\FourCorners\Http\Requests\CompleteAnnotationRequest;
use RobinsonRyan\FourCorners\Http\Requests\RejectAnnotationRequest;
use RobinsonRyan\FourCorners\Http\Requests\StartAnnotationRequest;
use RobinsonRyan\FourCorners\Http\Resources\AnnotationResource;
use RobinsonRyan\FourCorners\Http\Resources\DocumentTypeResource;
use RobinsonRyan\FourCorners\Http\Resources\RejectionReasonResource;
use RobinsonRyan\FourCorners\Models\DocumentType;
use RobinsonRyan\FourCorners\Models\RejectionReason;
use RobinsonRyan\FourCorners\Services\AnnotationService;

final class AnnotationController extends Controller
{
    public function __construct(
        private readonly AnnotationService $annotationService,
    ) {}

    /**
     * Get available document types and rejection reasons.
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'document_types' => DocumentTypeResource::collection(
                DocumentType::active()->get(),
            ),
            'rejection_reasons' => RejectionReasonResource::collection(
                RejectionReason::active()->ordered()->get(),
            ),
            'opencv_url' => config('four_corners.opencv_url'),
            'output' => config('four_corners.output'),
        ]);
    }

    /**
     * Start a new annotation.
     */
    public function start(StartAnnotationRequest $request): JsonResponse
    {
        $autoDetection = $request->has('auto_detection')
            ? AutoDetectionData::from($request->input('auto_detection'))
            : null;

        $annotation = $this->annotationService->start(
            originalImagePath: $request->input('original_image_path'),
            originalWidth: (int) $request->input('original_width'),
            originalHeight: (int) $request->input('original_height'),
            documentTypeId: $request->input('document_type_id'),
            autoDetection: $autoDetection,
        );

        return response()->json([
            'annotation' => new AnnotationResource($annotation),
        ], 201);
    }

    /**
     * Complete an annotation.
     */
    public function complete(CompleteAnnotationRequest $request, string $id): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $annotation = $this->annotationService->complete(
            annotationId: $id,
            finalCorners: CornersData::from($request->input('final_corners')),
            finalRotation: (int) $request->input('final_rotation'),
            annotatedBy: (int) $user->id,
            timeSpentSeconds: (float) $request->input('time_spent_seconds'),
            displayImageBase64: $request->input('display_image'),
            archiveImageBase64: $request->input('archive_image'),
        );

        return response()->json([
            'annotation' => new AnnotationResource($annotation),
            'message' => 'Annotation queued for processing',
        ]);
    }

    /**
     * Reject an annotation.
     */
    public function reject(RejectAnnotationRequest $request, string $id): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $annotation = $this->annotationService->reject(
            annotationId: $id,
            rejectionReasonId: $request->input('rejection_reason_id'),
            notes: $request->input('notes'),
            rejectedBy: (int) $user->id,
            timeSpentSeconds: (float) $request->input('time_spent_seconds'),
        );

        return response()->json([
            'annotation' => new AnnotationResource($annotation),
            'message' => 'Annotation rejected',
        ]);
    }

    /**
     * Get a single annotation.
     */
    public function show(string $id): JsonResponse
    {
        $annotation = $this->annotationService->find($id);

        if (! $annotation instanceof \RobinsonRyan\FourCorners\Models\DocumentAnnotation) {
            return response()->json([
                'message' => 'Annotation not found',
            ], 404);
        }

        return response()->json([
            'annotation' => new AnnotationResource($annotation->load(['documentType', 'rejectionReason'])),
        ]);
    }
}
