<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class TestCase
 *
 * Used for implementing common method across test cases
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Boot the testing helper traits.
     *
     * The method setUpTheTestEnvironmentTraits is sadly marked as final,
     * which forces me to override this method instead for conditionally loading migrations from request insurance
     * only if the RefreshDatabase trait is used.
     *
     * @return array
     */
    protected function setUpTraits(): array
    {
        $uses = \array_flip(\class_uses_recursive(static::class));

        if (isset($uses[RefreshDatabase::class])) {
            // Run request-insurance migrations
            $this->loadMigrationsFrom(__DIR__ . '/../vendor/cego/request-insurance/publishable/migrations');
        }

        return $this->setUpTheTestEnvironmentTraits($uses);
    }
}
