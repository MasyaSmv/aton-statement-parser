<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Tests;

use PHPUnit\Framework\TestCase;

final class FixtureLoadTest extends TestCase
{
    public function test_can_read_fixture_file(): void
    {
        $path = __DIR__ . '/Fixtures/sample.xml';

        $this->assertFileExists($path);

        $xml = file_get_contents($path);

        $this->assertIsString($xml);
        $this->assertNotSame('', trim($xml));
    }
}
