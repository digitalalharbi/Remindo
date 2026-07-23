<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Keeps the Unit test suite present (git does not track empty directories, so
 * without a tracked file here CI's fresh clone has no tests/Unit and PHPUnit
 * aborts with "Test directory not found").
 */
class ExampleTest extends TestCase
{
    public function test_arithmetic_sanity(): void
    {
        $this->assertSame(4, 2 + 2);
    }
}
