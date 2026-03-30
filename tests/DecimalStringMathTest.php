<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Tests;

use MasyaSmv\AtonStatementParser\Support\DecimalStringMath;
use PHPUnit\Framework\TestCase;

final class DecimalStringMathTest extends TestCase
{
    public function testAddsPositiveDecimalsWithoutPrecisionLoss(): void
    {
        $this->assertSame('11.55', DecimalStringMath::add('10.50', '1.05'));
        $this->assertSame('14348463.9600000000000', DecimalStringMath::add('14348463.9600000000000', '0'));
    }

    public function testAddsNegativeAndPositiveDecimals(): void
    {
        $this->assertSame('0.00', DecimalStringMath::add('10.00', '-10.00'));
        $this->assertSame('-8.95', DecimalStringMath::add('-10.00', '1.05'));
        $this->assertSame('-8.95', DecimalStringMath::add('1.05', '-10.00'));
        $this->assertSame('2', DecimalStringMath::add('5', '-3'));
    }
}
