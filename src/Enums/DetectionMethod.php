<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Enums;

enum DetectionMethod: string
{
    case OpenCVContour = 'opencv_js_contour_v1';
    case OpenCVFallback = 'opencv_js_fallback_v1';
    case MLModelV1 = 'ml_model_v1';
    case Manual = 'manual';
}
