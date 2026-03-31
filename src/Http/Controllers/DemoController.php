<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use RobinsonRyan\FourCorners\Models\DocumentType;
use RobinsonRyan\FourCorners\Models\RejectionReason;

final class DemoController extends Controller
{
    /**
     * Display the demo annotation page.
     */
    public function index(Request $request): Response
    {
        // Only use image URL if explicitly provided in query string
        $imageUrl = $request->query('image');

        return response()->view('four-corners::demo', [
            'imageUrl' => $imageUrl,
            'documentTypes' => DocumentType::active()->get(),
            'rejectionReasons' => RejectionReason::active()->ordered()->get(),
            'opencvUrl' => config('four_corners.opencv_url'),
        ]);
    }

    /**
     * Display a minimal OpenCV.js test page.
     */
    public function test(): Response
    {
        return response()->view('four-corners::test');
    }
}
