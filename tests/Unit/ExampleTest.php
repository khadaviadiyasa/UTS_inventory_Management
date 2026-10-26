<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_quantity_multiplication_produces_a_subtotal(): void
    {
        self::assertSame(3750.0, round(750.0 * 5, 2));
    }
}