<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Tests;

use BackedEnum;
use DateTimeInterface;
use Orchestra\Testbench\TestCase as Orchestra;
use RobinsonRyan\FourCorners\FourCornersServiceProvider;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
            FourCornersServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Set app key
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        // Database
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        // Package config
        $app['config']->set('four_corners.id_type', 'uuid7');

        // Override middleware for testing (remove auth requirement)
        $app['config']->set('four_corners.routes.middleware', ['web']);

        // Spatie Laravel Data config (v4.x)
        $app['config']->set('data', [
            'date_format' => DATE_ATOM,
            'date_timezone' => null,
            'features' => [
                'cast_and_transform_iterables' => false,
                'ignore_exception_when_trying_to_set_computed_property_value' => false,
            ],
            'transformers' => [
                DateTimeInterface::class => \Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer::class,
                \Illuminate\Contracts\Support\Arrayable::class => \Spatie\LaravelData\Transformers\ArrayableTransformer::class,
                BackedEnum::class => \Spatie\LaravelData\Transformers\EnumTransformer::class,
            ],
            'casts' => [
                DateTimeInterface::class => \Spatie\LaravelData\Casts\DateTimeInterfaceCast::class,
                BackedEnum::class => \Spatie\LaravelData\Casts\EnumCast::class,
            ],
            'rule_inferrers' => [
                \Spatie\LaravelData\RuleInferrers\SometimesRuleInferrer::class,
                \Spatie\LaravelData\RuleInferrers\NullableRuleInferrer::class,
                \Spatie\LaravelData\RuleInferrers\RequiredRuleInferrer::class,
                \Spatie\LaravelData\RuleInferrers\BuiltInTypesRuleInferrer::class,
                \Spatie\LaravelData\RuleInferrers\AttributesRuleInferrer::class,
            ],
            'normalizers' => [
                \Spatie\LaravelData\Normalizers\ModelNormalizer::class,
                \Spatie\LaravelData\Normalizers\ArrayableNormalizer::class,
                \Spatie\LaravelData\Normalizers\ObjectNormalizer::class,
                \Spatie\LaravelData\Normalizers\ArrayNormalizer::class,
                \Spatie\LaravelData\Normalizers\JsonNormalizer::class,
            ],
            'wrap' => null,
            'var_dumper_caster_mode' => 'development',
            'structure_caching' => [
                'enabled' => false,
                'directories' => [],
                'cache' => [
                    'store' => 'file',
                    'prefix' => 'laravel-data',
                    'duration' => null,
                ],
                'reflection_discovery' => [
                    'enabled' => true,
                    'base_path' => base_path(),
                    'root_namespace' => null,
                ],
            ],
            'validation_strategy' => \Spatie\LaravelData\Support\Creation\ValidationStrategy::OnlyRequests->value,
            'name_mapping_strategy' => [
                'input' => null,
                'output' => null,
            ],
            'ignore_invalid_partials' => false,
            'max_transformation_depth' => null,
            'throw_when_max_transformation_depth_reached' => true,
            'commands' => [
                'make' => [
                    'namespace' => 'Data',
                    'suffix' => 'Data',
                ],
            ],
            'livewire' => [
                'enable_synths' => false,
            ],
        ]);
    }

    protected function setUpDatabase(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
