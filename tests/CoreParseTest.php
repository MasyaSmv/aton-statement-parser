<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Tests;

use MasyaSmv\AtonStatementParser\AtonStatementParser;
use PHPUnit\Framework\TestCase;

final class CoreParseTest extends TestCase
{
    public function testCanParseSectionsAndOperIds(): void
    {
        $path = __DIR__ . '/Fixtures/sample.xml';

        $report = AtonStatementParser::fromFile($path);

        // Проверяем что есть секции и из них можно достать строки
        $this->assertTrue($report->hasSection('Trades') || $report->hasSection('MoneyInOut') || $report->hasSection('StockInOut'));

        $ids = $report->operIds();

        $this->assertGreaterThan(0, $ids->count());

        // Проверим, что каждый id - непустая строка
        foreach ($ids as $id) {
            $this->assertNotSame('', $id);
        }
    }
}
