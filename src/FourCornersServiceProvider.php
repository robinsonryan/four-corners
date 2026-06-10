<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners;

use Illuminate\Support\ServiceProvider;
use Override;
use RobinsonRyan\FourCorners\Contracts\AnnotationRepositoryInterface;
use RobinsonRyan\FourCorners\Repositories\EloquentAnnotationRepository;
use RobinsonRyan\FourCorners\Services\AnnotationService;
use RobinsonRyan\FourCorners\Services\MetricsCalculator;

final class FourCornersServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/four-corners.php',
            'four_corners',
        );

        $this->app->bind(AnnotationRepositoryInterface::class, EloquentAnnotationRepository::class);

        $this->app->singleton(MetricsCalculator::class);

        $this->app->singleton(AnnotationService::class, fn ($app): AnnotationService => new AnnotationService(
            $app->make(AnnotationRepositoryInterface::class),
            $app->make(MetricsCalculator::class),
        ));
    }

    public function boot(): void
    {
        $this->publishConfig();
        $this->publishMigrations();
        $this->publishSeeders();
        $this->publishComponents();
        $this->loadRoutes();
        $this->loadViews();
        $this->registerCommands();
    }

    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/four-corners.php' => config_path('four-corners.php'),
        ], 'four-corners-config');
    }

    protected function publishMigrations(): void
    {
        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'four-corners-migrations');
    }

    protected function publishSeeders(): void
    {
        $this->publishes([
            __DIR__.'/../database/seeders' => database_path('seeders'),
        ], 'four-corners-seeders');
    }

    protected function publishComponents(): void
    {
        // Vue Components
        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('js/vendor/four-corners'),
        ], 'four-corners-components');

        // CSS
        $this->publishes([
            __DIR__.'/../resources/css' => resource_path('css/vendor/four-corners'),
        ], 'four-corners-styles');

        // Publish all
        $this->publishes([
            __DIR__.'/../config/four-corners.php' => config_path('four-corners.php'),
            __DIR__.'/../database/seeders' => database_path('seeders'),
            __DIR__.'/../resources/js' => resource_path('js/vendor/four-corners'),
            __DIR__.'/../resources/css' => resource_path('css/vendor/four-corners'),
        ], 'four-corners');
    }

    protected function loadRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/four-corners.php');
    }

    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'four-corners');
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\ExportTrainingDataCommand::class,
            ]);
        }
    }
}
