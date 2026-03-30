<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Tests;

use DateTimeImmutable;
use MasyaSmv\AtonStatementParser\Dto\CommonData;
use MasyaSmv\AtonStatementParser\Dto\CorporateAction;
use MasyaSmv\AtonStatementParser\Dto\MoneyBalance;
use MasyaSmv\AtonStatementParser\Dto\MoneyConvertOperation;
use MasyaSmv\AtonStatementParser\Dto\MoneyOperation;
use MasyaSmv\AtonStatementParser\Dto\StockBalance;
use MasyaSmv\AtonStatementParser\Dto\StockPayingOff;
use MasyaSmv\AtonStatementParser\Dto\StockTransfer;
use MasyaSmv\AtonStatementParser\Dto\Trade;
use PHPUnit\Framework\TestCase;

final class DtoAccessorsTest extends TestCase
{
    public function testCommonDataAccessorsReturnConstructorValues(): void
    {
        $date = new DateTimeImmutable('2024-02-01 10:00:00');
        $dto = new CommonData('24260600', $date, $date, $date, 'CN-1', $date, true, false, 'ATON');

        $this->assertSame('24260600', $dto->cpId());
        $this->assertSame($date, $dto->begDate());
        $this->assertSame($date, $dto->endDate());
        $this->assertSame($date, $dto->makeDate());
        $this->assertSame('CN-1', $dto->contractNum());
        $this->assertSame($date, $dto->contractDate());
        $this->assertTrue($dto->withSubAccounts());
        $this->assertFalse($dto->isFilial());
        $this->assertSame('ATON', $dto->companyName());
    }

    public function testTradeAccessorsReturnConstructorValues(): void
    {
        $date = new DateTimeImmutable('2024-02-01 10:00:00');
        $dto = new Trade('1', 'Trades', 'TradeCommonSettled', true, 'Buy', 'Bond', '2', '3', 'USD', '4', 'RUR', $date, $date, $date, $date);

        $this->assertSame('1', $dto->operId());
        $this->assertSame('Trades', $dto->section());
        $this->assertSame('TradeCommonSettled', $dto->sourceName());
        $this->assertTrue($dto->isComplete());
        $this->assertSame('Buy', $dto->tradeType());
        $this->assertSame('Bond', $dto->assetName());
        $this->assertSame('2', $dto->quantity());
        $this->assertSame('3', $dto->price());
        $this->assertSame('USD', $dto->priceCurrency());
        $this->assertSame('4', $dto->payment());
        $this->assertSame('RUR', $dto->paymentCurrency());
        $this->assertSame($date, $dto->paymentDate());
        $this->assertSame($date, $dto->settlementDate());
        $this->assertSame($date, $dto->operDateSort());
        $this->assertSame($date, $dto->operTimeSort());
    }

    public function testMoneyOperationAccessorsReturnConstructorValues(): void
    {
        $date = new DateTimeImmutable('2024-02-01 10:00:00');
        $dto = new MoneyOperation('2', 'MoneyInOut', 'OperationMoneyBrok', 'Brok', '5', '6', 'RUR', 'Comment', $date, $date, $date);

        $this->assertSame('2', $dto->operId());
        $this->assertSame('MoneyInOut', $dto->section());
        $this->assertSame('OperationMoneyBrok', $dto->sourceName());
        $this->assertSame('Brok', $dto->operType());
        $this->assertSame('5', $dto->quantity());
        $this->assertSame('6', $dto->quantityRur());
        $this->assertSame('RUR', $dto->currency());
        $this->assertSame('Comment', $dto->comment());
        $this->assertSame($date, $dto->operDate());
        $this->assertSame($date, $dto->paymentDate());
        $this->assertSame($date, $dto->operDateSort());
    }

    public function testMoneyBalanceAccessorsReturnConstructorValues(): void
    {
        $dto = new MoneyBalance('MoneyOnDate', 'PortfolioMoney', 'RUR', 'Cash', 'P1', 'Main', '1', '2', '3', '4');

        $this->assertSame('MoneyOnDate', $dto->section());
        $this->assertSame('PortfolioMoney', $dto->sourceName());
        $this->assertSame('RUR', $dto->assetCode());
        $this->assertSame('Cash', $dto->name());
        $this->assertSame('P1', $dto->part());
        $this->assertSame('Main', $dto->partName());
        $this->assertSame('1', $dto->quantityBegin());
        $this->assertSame('2', $dto->quantityEnd());
        $this->assertSame('3', $dto->quantityBeginRur());
        $this->assertSame('4', $dto->quantityEndRur());
    }

    public function testStockBalanceAccessorsReturnConstructorValues(): void
    {
        $dto = new StockBalance('StockOnDate', 'PortfolioStockOTC', 'ISIN1', '1', '2', '3', '4', '5', '6', '7', 'USD', 'RUR', '8', '9', '10', '11');

        $this->assertSame('StockOnDate', $dto->section());
        $this->assertSame('PortfolioStockOTC', $dto->sourceName());
        $this->assertSame('ISIN1', $dto->assetCode());
        $this->assertSame('1', $dto->quantityIn());
        $this->assertSame('2', $dto->quantityInAvailable());
        $this->assertSame('3', $dto->quantityPlan());
        $this->assertSame('4', $dto->quantityOut());
        $this->assertSame('5', $dto->quantityOutAvailable());
        $this->assertSame('6', $dto->priceIn());
        $this->assertSame('7', $dto->priceOut());
        $this->assertSame('USD', $dto->currencyIn());
        $this->assertSame('RUR', $dto->currencyOut());
        $this->assertSame('8', $dto->valueIn());
        $this->assertSame('9', $dto->valueOut());
        $this->assertSame('10', $dto->nkdIn());
        $this->assertSame('11', $dto->nkdOut());
    }

    public function testMoneyConvertAccessorsReturnConstructorValues(): void
    {
        $date = new DateTimeImmutable('2024-02-01 10:00:00');
        $dto = new MoneyConvertOperation('3', 'MoneyConvert', 'TradeFXNonClient', 'USD', '10', '11', $date, '90', 'RUR', '900', '901', $date, $date);

        $this->assertSame('3', $dto->operId());
        $this->assertSame('MoneyConvert', $dto->section());
        $this->assertSame('TradeFXNonClient', $dto->sourceName());
        $this->assertSame('USD', $dto->currencyFrom());
        $this->assertSame('10', $dto->amountFrom());
        $this->assertSame('11', $dto->amountFromRur());
        $this->assertSame($date, $dto->dateFrom());
        $this->assertSame('90', $dto->rate());
        $this->assertSame('RUR', $dto->currencyTo());
        $this->assertSame('900', $dto->amountTo());
        $this->assertSame('901', $dto->amountToRur());
        $this->assertSame($date, $dto->dateTo());
        $this->assertSame($date, $dto->operDate());
    }

    public function testStockTransferAccessorsReturnConstructorValues(): void
    {
        $date = new DateTimeImmutable('2024-02-01 10:00:00');
        $dto = new StockTransfer('4', 'StockInOut', 'OperationStockInOut', 'Bond', '7', '8', 'Main', 'Comment', '42', $date, $date, $date, $date, $date);

        $this->assertSame('4', $dto->operId());
        $this->assertSame('StockInOut', $dto->section());
        $this->assertSame('OperationStockInOut', $dto->sourceName());
        $this->assertSame('Bond', $dto->assetName());
        $this->assertSame('7', $dto->quantity());
        $this->assertSame('8', $dto->price());
        $this->assertSame('Main', $dto->portfolio());
        $this->assertSame('Comment', $dto->comment());
        $this->assertSame('42', $dto->intOperNum());
        $this->assertSame($date, $dto->operDate());
        $this->assertSame($date, $dto->settlementDate());
        $this->assertSame($date, $dto->exSettlementDate());
        $this->assertSame($date, $dto->operDateSort());
        $this->assertSame($date, $dto->operTimeSort());
    }

    public function testStockPayingOffAccessorsReturnConstructorValues(): void
    {
        $date = new DateTimeImmutable('2024-02-01 10:00:00');
        $dto = new StockPayingOff('5', 'StockPayingOff', 'OperationStockPayOff', 'Bond', '3', '1000', 'USD', '3000', '3001', 'USD', '43', 'MTRT', 'Stk', 'Main', $date, $date, $date, $date, $date, $date);

        $this->assertSame('5', $dto->operId());
        $this->assertSame('StockPayingOff', $dto->section());
        $this->assertSame('OperationStockPayOff', $dto->sourceName());
        $this->assertSame('Bond', $dto->assetName());
        $this->assertSame('3', $dto->quantity());
        $this->assertSame('1000', $dto->nominal());
        $this->assertSame('USD', $dto->nominalCurrency());
        $this->assertSame('3000', $dto->payingSum());
        $this->assertSame('3001', $dto->payingSumRur());
        $this->assertSame('USD', $dto->paymentCurrency());
        $this->assertSame('43', $dto->intOperNum());
        $this->assertSame('MTRT', $dto->intOperType());
        $this->assertSame('Stk', $dto->groupId());
        $this->assertSame('Main', $dto->portfolio());
        $this->assertSame($date, $dto->operDate());
        $this->assertSame($date, $dto->paymentDate());
        $this->assertSame($date, $dto->settlementDate());
        $this->assertSame($date, $dto->exSettlementDate());
        $this->assertSame($date, $dto->operDateSort());
        $this->assertSame($date, $dto->operTimeSort());
    }

    public function testCorporateActionAccessorsReturnConstructorValues(): void
    {
        $date = new DateTimeImmutable('2024-02-01 10:00:00');
        $dto = new CorporateAction('6', 'CorpActionIn', 'OperationStockCorpActionIn', 'Bond', '1', '100', 'USD', '0', '1', 'Stk', 'Main', '44', $date, $date, $date, $date, $date, $date);

        $this->assertSame('6', $dto->operId());
        $this->assertSame('CorpActionIn', $dto->section());
        $this->assertSame('OperationStockCorpActionIn', $dto->sourceName());
        $this->assertSame('Bond', $dto->assetName());
        $this->assertSame('1', $dto->quantity());
        $this->assertSame('100', $dto->nominal());
        $this->assertSame('USD', $dto->nominalCurrency());
        $this->assertSame('0', $dto->payingSum());
        $this->assertSame('1', $dto->payingSumRur());
        $this->assertSame('Stk', $dto->groupId());
        $this->assertSame('Main', $dto->portfolio());
        $this->assertSame('44', $dto->intOperNum());
        $this->assertSame($date, $dto->operDate());
        $this->assertSame($date, $dto->paymentDate());
        $this->assertSame($date, $dto->settlementDate());
        $this->assertSame($date, $dto->exSettlementDate());
        $this->assertSame($date, $dto->operDateSort());
        $this->assertSame($date, $dto->operTimeSort());
    }
}
