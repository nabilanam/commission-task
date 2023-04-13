<?php

namespace App\Tests\Unit\Service;

use App\Service\Math;
use PHPUnit\Framework\TestCase;

class MathTest extends TestCase
{
    public function testAdd(): void
    {
        $math = new Math();

        $this->assertEquals('0.30', $math->add('0.1', '0.2', 2));
        $this->assertEquals('0.3', $math->add('0.1', '0.2', 1));
        $this->assertEquals('0', $math->add('0.1', '0.2', 0));
    }

    public function testExceptionForAddWhenPrecisionIsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $math = new Math();
        $math->add('1', '1', -1);
    }

    public function testSubtract()
    {
        $math = new Math();

        $this->assertEquals('0.10', $math->subtract('0.3', '0.2', 2));
        $this->assertEquals('-0.10', $math->subtract('0.2', '0.3', 2));
        $this->assertEquals('0.1', $math->subtract('0.3', '0.2', 1));
        $this->assertEquals('0', $math->subtract('0.3', '0.2', 0));
    }

    public function testExceptionForSubtractWhenPrecisionIsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $math = new Math();
        $math->subtract('1', '1', -1);
    }

    public function testMultiply()
    {
        $math = new Math();

        $this->assertEquals('0.06', $math->multiply('0.3', '0.2', 2));
        $this->assertEquals('0.6', $math->multiply('0.3', '2', 1));
        $this->assertEquals('0', $math->multiply('0.3', '0.2', 0));
    }

    public function testExceptionForMultiplyWhenPrecisionIsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $math = new Math();
        $math->multiply('1', '1', -1);
    }

    public function testDivide()
    {
        $math = new Math();

        $this->assertEquals('0.15', $math->divide('0.3', '2', 2));
        $this->assertEquals('0.2', $math->divide('0.3', '1.5', 1));
    }

    public function testExceptionForDivideWhenPrecisionIsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $math = new Math();
        $math->divide('1', '1', -1);
    }

    public function testExceptionForDivideWhenDivisorIsZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $math = new Math();
        $math->divide('1', '0', 2);
    }

    /**
     * @dataProvider roundUpDataProvider
     */
    public function testRoundUp($expected, $given, $precision): void
    {
        $math = new Math();

        $this->assertEquals($expected, $math->roundUp($given, $precision));
    }

    public function testExceptionForRoundUpWhenPrecisionIsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $math = new Math();
        $math->roundUp('1', -1);
    }

    public function testCompare(): void
    {
        $math = new Math();

        $this->assertEquals(0, $math->compare('1', '1', 0));
        $this->assertEquals(1, $math->compare('2', '1', 0));
        $this->assertEquals(-1, $math->compare('1', '2', 0));

        $this->assertEquals(0, $math->compare('0.1', '0.1', 2));
        $this->assertEquals(1, $math->compare('0.2', '0.1', 2));
        $this->assertEquals(-1, $math->compare('0.1', '0.2', 2));
    }

    public function testExceptionForCompareWhenPrecisionIsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $math = new Math();
        $math->compare('1', '1', -1);
    }

    public function roundUpDataProvider(): array
    {
        return [
            ['1', '0.009', 0],
            ['1', '0.99', 0],
            ['1', '1.00', 0],
            ['2', '1.001', 0],
            ['2', '1.005', 0],
            ['2', '1.009', 0],

            ['0.01', '0.009', 2],
            ['0.99', '0.99', 2],
            ['1.00', '1.00', 2],
            ['1.01', '1.001', 2],
            ['1.01', '1.005', 2],
            ['1.01', '1.009', 2],

            ['0.010', '0.010', 3],
            ['0.990', '0.99', 3],
            ['1.001', '1.001', 3],
            ['1.001', '1.0001', 3],
            ['1.000', '1.0000', 3],
            ['1.010', '1.010', 3],
        ];
    }
}
