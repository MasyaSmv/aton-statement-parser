<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Tests;

use MasyaSmv\AtonStatementParser\AtonStatementParser;
use PHPUnit\Framework\TestCase;

final class DtoMappingTest extends TestCase
{
    public function testCanMapCommonDataTradeAndMoneyDtosFromModernFixture(): void
    {
        $path = __DIR__ . '/FixturesLocal/new/report_24260600_20240201_20240212_client.xml';

        if (!is_file($path)) {
            $this->markTestSkipped('Local modern fixture is not available.');
        }

        $report = AtonStatementParser::fromFile($path);

        $commonData = $report->commonData();
        $this->assertNotNull($commonData);
        $this->assertSame('24260600', $commonData->cpId());
        $this->assertSame('2024-02-01', $commonData->begDate()?->format('Y-m-d'));
        $this->assertSame('2024-02-12', $commonData->endDate()?->format('Y-m-d'));
        $this->assertSame(true, $commonData->withSubAccounts());

        $trades = $report->trades();
        $this->assertGreaterThan(0, $trades->count());
        $trade = $trades->first();
        $this->assertNotNull($trade);
        $this->assertSame('Trades', $trade->section());
        $this->assertSame('TradeCommonNotSettled', $trade->sourceName());
        $this->assertSame('496218886', $trade->operId());

        $moneyOperations = $report->moneyInOut();
        $this->assertGreaterThan(0, $moneyOperations->count());
        $moneyOperation = $moneyOperations->first();
        $this->assertNotNull($moneyOperation);
        $this->assertSame('MoneyInOut', $moneyOperation->section());
        $this->assertSame('OperationMoneyDepo', $moneyOperation->sourceName());
        $this->assertSame('Depo', $moneyOperation->operType());
    }

    public function testCanMapRepoAndMoneyIoDtosFromModernFixture(): void
    {
        $path = __DIR__ . '/FixturesLocal/new/report_30385480_20241228_20251228_client.xml';

        if (!is_file($path)) {
            $this->markTestSkipped('Local modern repo fixture is not available.');
        }

        $report = AtonStatementParser::fromFile($path);

        $trades = $report->trades();
        $this->assertGreaterThan(0, $trades->count());

        $repoTrade = null;

        foreach ($trades as $trade) {
            if ($trade->section() === 'TradesRegRepo') {
                $repoTrade = $trade;
                break;
            }
        }

        $this->assertNotNull($repoTrade);
        $this->assertSame('TradeRepoSettled', $repoTrade->sourceName());
        $this->assertSame('746041411', $repoTrade->operId());

        $moneyOperations = $report->moneyInOut();
        $this->assertGreaterThan(0, $moneyOperations->count());

        $moneyIo = null;

        foreach ($moneyOperations as $operation) {
            if ($operation->section() === 'MoneyInOut_io') {
                $moneyIo = $operation;
                break;
            }
        }

        $this->assertNotNull($moneyIo);
        $this->assertSame('OperationMoneyInOut', $moneyIo->sourceName());
        $this->assertSame('InOut', $moneyIo->operType());
    }

    public function testCanMapMoneyConvertAndStockTransferDtosFromModernFixture(): void
    {
        $path = __DIR__ . '/FixturesLocal/new/report_22824900_20201231_20211231_client.xml';

        if (!is_file($path)) {
            $this->markTestSkipped('Local modern FX fixture is not available.');
        }

        $report = AtonStatementParser::fromFile($path);

        $moneyConvert = $report->moneyConvert();
        $this->assertGreaterThan(0, $moneyConvert->count());

        $clientConvert = null;
        $nonClientConvert = null;

        foreach ($moneyConvert as $operation) {
            if ($operation->section() === 'ClientMoneyConvert') {
                $clientConvert = $operation;
            }

            if ($operation->section() === 'MoneyConvert') {
                $nonClientConvert = $operation;
            }
        }

        $this->assertNotNull($clientConvert);
        $this->assertSame('293065579', $clientConvert->operId());
        $this->assertSame('RUR', $clientConvert->currencyFrom());
        $this->assertSame('22252.50000000', $clientConvert->amountFrom());
        $this->assertSame('USD', $clientConvert->currencyTo());

        $this->assertNotNull($nonClientConvert);
        $this->assertSame('305947032', $nonClientConvert->operId());
        $this->assertSame('4971.30000000', $nonClientConvert->amountFrom());
        $this->assertSame('366403.00000000', $nonClientConvert->amountToRur());

        $stockTransfers = $report->stockInOut();
        $this->assertGreaterThan(0, $stockTransfers->count());

        $stockTransfer = $stockTransfers->first();
        $this->assertNotNull($stockTransfer);
        $this->assertSame('397850086', $stockTransfer->operId());
        $this->assertSame('OperationStockInOut', $stockTransfer->sourceName());
        $this->assertSame('43.78433000', $stockTransfer->quantity());
        $this->assertSame('2021-12-27', $stockTransfer->operDateSort()?->format('Y-m-d'));
    }

    public function testCanMapStockPayingOffAndCorporateActionsFromModernFixture(): void
    {
        $path = __DIR__ . '/FixturesLocal/new/report_22824900_20200101_20201231_client.xml';

        if (!is_file($path)) {
            $this->markTestSkipped('Local modern stock operations fixture is not available.');
        }

        $report = AtonStatementParser::fromFile($path);

        $stockPayingOff = $report->stockPayingOff();
        $this->assertGreaterThan(0, $stockPayingOff->count());

        $payingOff = $stockPayingOff->first();
        $this->assertNotNull($payingOff);
        $this->assertSame('268875709', $payingOff->operId());
        $this->assertSame('MTRT', $payingOff->intOperType());
        $this->assertSame('3906405.00000000', $payingOff->payingSumRur());

        $corpActionsIn = $report->corporateActionsIn();
        $this->assertGreaterThan(0, $corpActionsIn->count());

        $corpActionIn = $corpActionsIn->first();
        $this->assertNotNull($corpActionIn);
        $this->assertSame('266182921', $corpActionIn->operId());
        $this->assertSame('Stk', $corpActionIn->groupId());
        $this->assertSame('0.000000', $corpActionIn->payingSumRur());

        $corpActionsOut = $report->corporateActionsOut();
        $this->assertGreaterThan(0, $corpActionsOut->count());

        $corpActionOut = $corpActionsOut->first();
        $this->assertNotNull($corpActionOut);
        $this->assertSame('266183234', $corpActionOut->operId());
        $this->assertSame('CorpActionOut', $corpActionOut->section());
        $this->assertSame('Stk', $corpActionOut->groupId());
    }

    public function testCanMapMoneyAndStockBalancesFromModernFixture(): void
    {
        $moneyPath = __DIR__ . '/FixturesLocal/new/report_24260600_20240201_20240212_client.xml';
        $metalPath = __DIR__ . '/FixturesLocal/new/report_24467800_20221231_20231110_client.xml';

        if (!is_file($moneyPath) || !is_file($metalPath)) {
            $this->markTestSkipped('Local modern balance fixtures are not available.');
        }

        $report = AtonStatementParser::fromFile($moneyPath);

        $moneyBalances = $report->moneyOnDate();
        $this->assertGreaterThan(0, $moneyBalances->count());

        $moneyBalance = $moneyBalances->first();
        $this->assertNotNull($moneyBalance);
        $this->assertSame('RUR', $moneyBalance->assetCode());
        $this->assertSame('Available', $moneyBalance->part());
        $this->assertSame('2231.28', $moneyBalance->quantityEnd());
        $this->assertSame('2231.28', $moneyBalance->quantityEndRur());

        $stockBalances = $report->stockOnDate();
        $this->assertGreaterThan(0, $stockBalances->count());

        $stockBalance = $stockBalances->first();
        $this->assertNotNull($stockBalance);
        $this->assertSame('StockOnDate', $stockBalance->section());
        $this->assertSame('Apple Inc(C)/US0378331005/', $stockBalance->assetCode());
        $this->assertSame('2.00000000', $stockBalance->quantityOut());
        $this->assertSame('187.15000000', $stockBalance->priceOut());

        $metalReport = AtonStatementParser::fromFile($metalPath);
        $metalBalances = $metalReport->stockOnDate();
        $this->assertGreaterThan(0, $metalBalances->count());

        $metalBalance = null;

        foreach ($metalBalances as $balance) {
            if ($balance->section() === 'StockOnDate_MTL') {
                $metalBalance = $balance;
                break;
            }
        }

        $this->assertNotNull($metalBalance);
        $this->assertSame('GOLD', $metalBalance->assetCode());
        $this->assertSame('11.00000000', $metalBalance->quantityOut());
        $this->assertSame('5676.89000000', $metalBalance->priceOut());
    }
}
