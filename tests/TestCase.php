<?php

namespace Tests;

use App\Models\StoreCurrency;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable exception handling here for debugging errors if needed
        // $this->withoutExceptionHandling();
    }

    /**
     * The store's base currency.
     *
     * DatabaseSeeder seeds one, because a store with no base currency is not a usable state.
     * Tests therefore reuse that row rather than creating a second USD and tripping the unique
     * index on `code`.
     */
    protected function baseCurrency(): StoreCurrency
    {
        return StoreCurrency::firstWhere('is_base', true)
            ?? StoreCurrency::factory()->base()->create();
    }
}
