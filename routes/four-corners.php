<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use RobinsonRyan\FourCorners\Http\Controllers\AnnotationController;
use RobinsonRyan\FourCorners\Http\Controllers\DemoController;

// Serve OpenCV.js locally (no auth required for assets)
Route::get('four-corners/opencv.js', function () {
    $path = __DIR__.'/../resources/js/vendor/opencv.js';
    if (! file_exists($path)) {
        abort(404, 'OpenCV.js not found');
    }

    return response()->file($path, [
        'Content-Type' => 'application/javascript',
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->middleware('web')->name('four-corners.opencv');

Route::group([
    'prefix' => config('four_corners.routes.prefix', 'admin/annotations'),
    'middleware' => config('four_corners.routes.middleware', ['web', 'auth']),
    'as' => config('four_corners.routes.name_prefix', 'four-corners.'),
], function (): void {
    // Demo page (for testing the annotation workflow)
    Route::get('/demo', [DemoController::class, 'index'])
        ->name('demo');

    // OpenCV.js test page (minimal example for debugging)
    Route::get('/test', [DemoController::class, 'test'])
        ->name('test');

    // Configuration endpoint (document types, rejection reasons)
    Route::get('/config', [AnnotationController::class, 'config'])
        ->name('config');

    // Annotation CRUD
    Route::post('/', [AnnotationController::class, 'start'])
        ->name('start');

    Route::get('/{id}', [AnnotationController::class, 'show'])
        ->name('show');

    Route::post('/{id}/complete', [AnnotationController::class, 'complete'])
        ->name('complete');

    Route::post('/{id}/reject', [AnnotationController::class, 'reject'])
        ->name('reject');
});
