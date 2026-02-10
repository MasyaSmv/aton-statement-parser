<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Tests;

use MasyaSmv\AtonStatementParser\AtonStatementParser;
use PHPUnit\Framework\TestCase;

/**
 * Smoke test.
 *
 * Цель:
 * - проверить, что Composer PSR-4 автолоад настроен правильно;
 * - PHPUnit видит тесты и запускается;
 * - класс пакета доступен.
 */
final class AtonStatementParserTest extends TestCase
{
    public function test_version_returns_string(): void
    {
        $version = AtonStatementParser::version();

        $this->assertIsString($version);
        $this->assertNotSame('', $version);
        $this->assertSame('0.1.0', $version);
    }
}
