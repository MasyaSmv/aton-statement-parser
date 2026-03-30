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
        $this->assertSame('24260600', $commonRow->getString('CPID'));
        $this->assertSame('01.02.2024', $commonRow->getString('BegDate'));
        $this->assertSame('12.02.2024', $commonRow->getString('EndDate'));
        $this->assertSame('1', $commonRow->getString('WithSubAccounts'));

        $reportDate = $commonRow->getDate('ReportDate');
        $this->assertInstanceOf(DateTimeImmutable::class, $reportDate);
        $this->assertSame('2026-03-05 00:00:00', $reportDate->format('Y-m-d H:i:s'));

        $tradeRow = $report->section('Trades')->rows()->first();
        $this->assertNotNull($tradeRow);
        $this->assertSame('TradeCommonNotSettled', $tradeRow->sourceName());
        $this->assertSame('Trades', $tradeRow->section());
        $this->assertSame('0', $tradeRow->getString('isComplete'));
        $this->assertSame('09.02.2024 0:00:00', $tradeRow->getString('OperDateSort'));
        $this->assertSame('01.01.1900 0:00:00', $tradeRow->getString('OperTimeSort'));

        $moneyRow = $report->findOperId('495079837');
        $this->assertNotNull($moneyRow);
        $this->assertSame('OperationMoneyInOut', $moneyRow->sourceName());
        $this->assertSame('InOut', $moneyRow->getString('OperType'));
        $this->assertSame('10', $moneyRow->getString('OperTypeSort'));
        $this->assertSame('935.01000000', $moneyRow->getDecimalString('Quantity'));
        $this->assertSame('935.01000000', $moneyRow->getDecimalString('Quantity_RUR'));
    }

    public function testModernFormatCanMapRepoAndStockTransferSections(): void
    {
        $path = __DIR__ . '/FixturesLocal/new/report_30385480_20241228_20251228_client.xml';

        if (!is_file($path)) {
            $this->markTestSkipped('Local modern repo fixture is not available.');
        }

        $report = AtonStatementParser::fromFile($path);

        $this->assertTrue($report->hasSection('TradesRegRepo'));
        $this->assertTrue($report->hasSection('TradesNonRegRepo'));
        $this->assertTrue($report->hasSection('StockInOut'));

        $repoSettled = $report->section('TradesRegRepo')->rows()->first();
        $this->assertNotNull($repoSettled);
        $this->assertSame('TradeRepoSettled', $repoSettled->sourceName());
        $this->assertSame('1', $repoSettled->getString('isComplete'));
        $this->assertSame('25.12.2025 0:00:00', $repoSettled->getString('OperDateSort'));
        $this->assertSame('01.01.1900 14:55:03', $repoSettled->getString('OperTimeSort'));
        $this->assertSame('24', $repoSettled->getString('RepoMarginCall'));

        $repoNotSettled = $report->section('TradesNonRegRepo')->rows()->first();
        $this->assertNotNull($repoNotSettled);
        $this->assertSame('TradeRepoNotSettled', $repoNotSettled->sourceName());
        $this->assertSame('0', $repoNotSettled->getString('isComplete'));
        $this->assertSame('24', $repoNotSettled->getString('RepoMarginCall'));

        $stockInOut = $report->section('StockInOut')->rows()->first();
        $this->assertNotNull($stockInOut);
        $this->assertSame('OperationStockInOut', $stockInOut->sourceName());
        $this->assertSame('StockInOut', $stockInOut->getString('Section'));
        $this->assertSame('', $stockInOut->getString('Price'));
        $this->assertSame('24.12.2025 0:00:00', $stockInOut->getString('OperDateSort'));
    }

    public function testModernFormatCanMapFxMetalAndPayOffData(): void
    {
        $fxPath = __DIR__ . '/FixturesLocal/new/report_22824900_20201231_20211231_client.xml';
        $payOffPath = __DIR__ . '/FixturesLocal/new/report_22824900_20200101_20201231_client.xml';
        $metalPath = __DIR__ . '/FixturesLocal/new/report_24467800_20221231_20231110_client.xml';

        if (!is_file($fxPath) || !is_file($payOffPath) || !is_file($metalPath)) {
            $this->markTestSkipped('Local modern fixtures for FX/metal/payoff are not available.');
        }

        $fxReport = AtonStatementParser::fromFile($fxPath);
        $this->assertTrue($fxReport->hasSection('ClientMoneyConvert'));
        $this->assertTrue($fxReport->hasSection('MoneyConvert'));

        $clientMoneyConvert = $fxReport->section('ClientMoneyConvert')->rows()->first();
        $this->assertNotNull($clientMoneyConvert);
        $this->assertSame('TradeFXClient', $clientMoneyConvert->sourceName());
        $this->assertSame('ClientMoneyConvert', $clientMoneyConvert->getString('Section'));
        $this->assertSame('22.03.2021', $clientMoneyConvert->getString('OperDate'));
        $this->assertSame('22.03.2021 0:00:00', $clientMoneyConvert->getString('OperDateSort'));
        $this->assertSame('22252.50000000', $clientMoneyConvert->getDecimalString('Sum1'));
        $this->assertSame('22252.50000000', $clientMoneyConvert->getDecimalString('Sum1_RUR'));

        $moneyConvert = $fxReport->section('MoneyConvert')->rows()->first();
        $this->assertNotNull($moneyConvert);
        $this->assertSame('TradeFXNonClient', $moneyConvert->sourceName());
        $this->assertSame('MoneyConvert', $moneyConvert->getString('Section'));
        $this->assertSame('366403.00000000', $moneyConvert->getDecimalString('Sum2_RUR'));

        $payOffReport = AtonStatementParser::fromFile($payOffPath);
        $this->assertTrue($payOffReport->hasSection('StockPayingOff'));
        $payOff = $payOffReport->findOperId('268875709');
        $this->assertNotNull($payOff);
        $this->assertSame('OperationStockPayOff', $payOff->sourceName());
        $this->assertSame('StockPayingOff', $payOff->getString('Section'));
        $this->assertSame('Stk', $payOff->getString('GroupID'));
        $this->assertSame('MTRT', $payOff->getString('IntOperType'));
        $this->assertSame('3906405.00000000', $payOff->getDecimalString('PayingSum_RUR'));

        $metalReport = AtonStatementParser::fromFile($metalPath);
        $this->assertTrue($metalReport->hasSection('StockOnDate_MTL'));
        $metalRow = $metalReport->section('StockOnDate_MTL')->rows()->first();
        $this->assertNotNull($metalRow);
        $this->assertSame('PortfolioStockMetal', $metalRow->sourceName());
        $this->assertSame('GOLD', $metalRow->getString('AssetCode'));
        $this->assertSame('11.00000000', $metalRow->getDecimalString('QttyOut'));
        $this->assertSame('5676.89000000', $metalRow->getDecimalString('PriceEnd'));
        $this->assertSame('5676.89000000', $metalRow->getDecimalString('PriceOut'));
    }
}
