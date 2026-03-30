<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Tests;

use DateTimeImmutable;
use MasyaSmv\AtonStatementParser\AtonStatementParser;
use PHPUnit\Framework\TestCase;

final class MultiFormatParseTest extends TestCase
{
    public function testCanParseLegacyBisFormatFromLocalFixture(): void
    {
        $path = __DIR__ . '/FixturesLocal/old/report_24260600_20240201_20240212_client.xml';

        if (!is_file($path)) {
            $this->markTestSkipped('Local legacy fixture is not available.');
        }

        $report = AtonStatementParser::fromFile($path);

        $this->assertTrue($report->hasSection('Trades'));
        $this->assertTrue($report->hasSection('MoneyInOut'));
        $this->assertTrue($report->hasSection('CommonData'));
        $this->assertGreaterThan(0, $report->operIds()->count());

        $tradeRow = $report->section('Trades')->rows()->first();
        $this->assertNotNull($tradeRow);
        $this->assertSame('Trades', $tradeRow->section());
        $this->assertSame('Trades', $tradeRow->sourceName());
    }

    public function testCanParseModernFormatFromLocalFixture(): void
    {
        $path = __DIR__ . '/FixturesLocal/new/report_24260600_20240201_20240212_client.xml';

        if (!is_file($path)) {
            $this->markTestSkipped('Local modern fixture is not available.');
        }

        $report = AtonStatementParser::fromFile($path);

        $this->assertTrue($report->hasSection('CommonData'));
        $this->assertTrue($report->hasSection('Trades'));
        $this->assertTrue($report->hasSection('MoneyInOut'));
        $this->assertTrue($report->hasSection('MoneyOnDate'));
        $this->assertTrue($report->hasSection('StockOnDate'));
        $this->assertTrue($report->hasSection('StockOnDate_NonExg'));
        $this->assertGreaterThan(0, $report->operIds()->count());

        $commonRow = $report->section('CommonData')->rows()->first();
        $this->assertNotNull($commonRow);
        $this->assertSame('Header', $commonRow->sourceName());
        $this->assertSame('Header', $commonRow->recordType());
        $this->assertSame('24260600', $commonRow->getString('CpID'));

        $reportDate = $commonRow->getDate('ReportDate');
        $this->assertInstanceOf(DateTimeImmutable::class, $reportDate);
        $this->assertSame('2026-03-05 00:00:00', $reportDate->format('Y-m-d H:i:s'));

        $tradeRow = $report->section('Trades')->rows()->first();
        $this->assertNotNull($tradeRow);
        $this->assertSame('TradeCommonNotSettled', $tradeRow->sourceName());
        $this->assertSame('Trades', $tradeRow->section());

        $moneyRow = $report->findOperId('495079837');
        $this->assertNotNull($moneyRow);
        $this->assertSame('OperationMoneyInOut', $moneyRow->sourceName());
        $this->assertSame('935.01000000', $moneyRow->getDecimalString('Quantity'));
    }
}
