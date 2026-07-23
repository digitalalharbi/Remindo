<?php

namespace Tests;

use App\Support\Tenancy;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tenancy is a static holder; reset it so state never leaks between tests.
        Tenancy::clear();
    }

    protected function tearDown(): void
    {
        Tenancy::clear();
        parent::tearDown();
    }
}
