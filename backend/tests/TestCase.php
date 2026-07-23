<?php

namespace Tests;

use App\Support\Tenancy;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tenancy is a static holder; reset it so state never leaks between tests.
        Tenancy::clear();

        // Throttle counters live in the cache store (redis in CI). Flush it so
        // rate-limit state from one test never bleeds into the next (e.g. the
        // throttled /contact endpoint returning 429 instead of a 422).
        try {
            Cache::store()->flush();
        } catch (\Throwable) {
            // Cache store unavailable in a given context — nothing to reset.
        }
    }

    protected function tearDown(): void
    {
        Tenancy::clear();
        parent::tearDown();
    }
}
