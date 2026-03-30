<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Tests;

use IteratorAggregate;
use MasyaSmv\AtonStatementParser\Collections\CorporateActionCollection;
use MasyaSmv\AtonStatementParser\Collections\MoneyBalanceCollection;
use MasyaSmv\AtonStatementParser\Collections\MoneyConvertCollection;
use MasyaSmv\AtonStatementParser\Collections\MoneyOperationCollection;
use MasyaSmv\AtonStatementParser\Collections\OperIdCollection;
use MasyaSmv\AtonStatementParser\Collections\RowCollection;
use MasyaSmv\AtonStatementParser\Collections\StockBalanceCollection;
use MasyaSmv\AtonStatementParser\Collections\StockPayingOffCollection;
use MasyaSmv\AtonStatementParser\Collections\StockTransferCollection;
use MasyaSmv\AtonStatementParser\Collections\TradeCollection;
use MasyaSmv\AtonStatementParser\Dto\CorporateAction;
use MasyaSmv\AtonStatementParser\Dto\MoneyBalance;
use MasyaSmv\AtonStatementParser\Dto\MoneyConvertOperation;
use MasyaSmv\AtonStatementParser\Dto\MoneyOperation;
use MasyaSmv\AtonStatementParser\Dto\StockBalance;
use MasyaSmv\AtonStatementParser\Dto\StockPayingOff;
use MasyaSmv\AtonStatementParser\Dto\StockTransfer;
use MasyaSmv\AtonStatementParser\Dto\Trade;
use MasyaSmv\AtonStatementParser\Report\AttributeBag;
use MasyaSmv\AtonStatementParser\Report\Report;
use MasyaSmv\AtonStatementParser\Report\Row;
use MasyaSmv\AtonStatementParser\Report\Section;
use PHPUnit\Framework\TestCase;

final class CoverageSmokeTest extends TestCase
{
    public function testAttributeBagCanReadValuesAndIterate(): void
    {
        $bag = new AttributeBag(['A' => '1', 'B' => '2']);

        $this->assertCount(2, $bag);
        $this->assertTrue($bag->has('A'));
        $this->assertFalse($bag->has('C'));
        $this->assertSame('1', $bag->get('A'));
        $this->assertSame('fallback', $bag->get('C', 'fallback'));
        $this->assertSame(['A' => '1', 'B' => '2'], $bag->toArray());
        $this->assertTrue($bag->offsetExists('A'));
        $this->assertFalse($bag->offsetExists('C'));
        $this->assertSame('2', $bag->offsetGet('B'));
        $this->assertNull($this->callOffsetGet($bag, 123));
        $this->assertSame(['A' => '1', 'B' => '2'], iterator_to_array($bag->getIterator()));
    }

    public function testRowAndSectionExposeMetadataAndTypedAccessors(): void
    {
        $row = new Row(
            'Trades',
            'TradeCommonSettled',
            'TradeRow',
            new AttributeBag([
                'OperID' => '77',
                'IntValue' => '100',
                'FloatValue' => '10,25',
                'DecimalValue' => ' 15.5000 ',
                'BoolValue' => 'true',
                'DateValue' => '2024-02-01T00:00:00',
            ])
        );

        $section = new Section('Trades', new RowCollection([$row]));

        $this->assertSame('Trades', $section->name());
        $this->assertSame('Trades', $row->section());
        $this->assertSame('TradeCommonSettled', $row->sourceName());
        $this->assertSame('TradeRow', $row->recordType());
        $this->assertSame('77', $row->attributes()->get('OperID'));
        $this->assertTrue($row->has('IntValue'));
        $this->assertFalse($row->has('Missing'));
        $this->assertSame('fallback', $row->getString('Missing', 'fallback'));
        $this->assertSame(100, $row->getInt('IntValue'));
        $this->assertSame(42, $row->getInt('Missing', 42));
        $this->assertSame(10.25, $row->getFloat('FloatValue'));
        $this->assertSame(1.5, $row->getFloat('Missing', 1.5));
        $this->assertSame('15.5000', $row->getDecimalString('DecimalValue'));
        $this->assertSame('9.9', $row->getDecimalString('Missing', '9.9'));
        $this->assertTrue($row->getBool('BoolValue'));
        $this->assertFalse($row->getBool('Missing', false));
        $this->assertSame('2024-02-01', $row->getDate('DateValue')?->format('Y-m-d'));
        $this->assertCount(1, $section->rows());
    }

    public function testCollectionsProvideHappyPathAccess(): void
    {
        $row = new Row('Trades', 'Trades', 'Row', new AttributeBag(['OperID' => '1']));
        $trade = new Trade('1', 'Trades', 'TradeCommonSettled', true, 'Buy', 'Asset', '2', '3', 'USD', '4', 'RUR', null, null, null, null);
        $moneyOperation = new MoneyOperation('2', 'MoneyInOut', 'OperationMoneyBrok', 'Brok', '5', '5', 'RUR', 'Comment', null, null, null);
        $moneyConvert = new MoneyConvertOperation('3', 'MoneyConvert', 'TradeFXNonClient', 'USD', '10', '10', null, '90', 'RUR', '900', '900', null, null);
        $moneyBalance = new MoneyBalance('MoneyOnDate', 'PortfolioMoney', 'RUR', 'Cash', 'P1', 'Main', '1', '2', '1', '2');
        $stockBalance = new StockBalance('StockOnDate', 'PortfolioStockOTC', 'US0000000001', '1', '1', '0', '2', '2', '10', '11', 'USD', 'USD', '10', '22', '0', '0');
        $stockTransfer = new StockTransfer('4', 'StockInOut', 'OperationStockInOut', 'Bond', '7', '8', 'Main', 'Comment', '42', null, null, null, null, null);
        $stockPayingOff = new StockPayingOff('5', 'StockPayingOff', 'OperationStockPayOff', 'Bond', '3', '1000', 'USD', '3000', '3000', 'USD', '43', 'MTRT', 'Stk', 'Main', null, null, null, null, null, null);
        $corporateAction = new CorporateAction('6', 'CorpActionIn', 'OperationStockCorpActionIn', 'Bond', '1', '100', 'USD', '0', '0', 'Stk', 'Main', '44', null, null, null, null, null, null);

        $rowCollection = new RowCollection([$row]);
        $operIdCollection = new OperIdCollection(['1', '2']);
        $tradeCollection = new TradeCollection([$trade]);
        $moneyOperationCollection = new MoneyOperationCollection([$moneyOperation]);
        $moneyConvertCollection = new MoneyConvertCollection([$moneyConvert]);
        $moneyBalanceCollection = new MoneyBalanceCollection([$moneyBalance]);
        $stockBalanceCollection = new StockBalanceCollection([$stockBalance]);
        $stockTransferCollection = new StockTransferCollection([$stockTransfer]);
        $stockPayingOffCollection = new StockPayingOffCollection([$stockPayingOff]);
        $corporateActionCollection = new CorporateActionCollection([$corporateAction]);

        $this->assertFalse($rowCollection->isEmpty());
        $this->assertSame($row, $rowCollection->first());
        $this->assertSame($row, $rowCollection->get(0));
        $this->assertTrue($rowCollection->offsetExists(0));
        $this->assertSame($row, $rowCollection->offsetGet(0));
        $this->assertSame([$row], $rowCollection->toArray());
        $this->assertInstanceOf(IteratorAggregate::class, $rowCollection);

        $this->assertFalse($operIdCollection->isEmpty());
        $this->assertSame('1', $operIdCollection->first());
        $this->assertSame('2', $operIdCollection->get(1));
        $this->assertTrue($operIdCollection->offsetExists(1));
        $this->assertSame('1', $operIdCollection->offsetGet(0));
        $this->assertSame(['1', '2'], $operIdCollection->toArray());

        $this->assertSame($trade, $tradeCollection->first());
        $this->assertSame($trade, $tradeCollection->get(0));
        $this->assertTrue($tradeCollection->offsetExists(0));
        $this->assertSame($trade, $tradeCollection->offsetGet(0));
        $this->assertSame([$trade], $tradeCollection->toArray());

        $this->assertSame($moneyOperation, $moneyOperationCollection->first());
        $this->assertSame($moneyOperation, $moneyOperationCollection->offsetGet(0));
        $this->assertSame([$moneyOperation], $moneyOperationCollection->toArray());
        $this->assertTrue($moneyOperationCollection->offsetExists(0));
        $this->assertFalse($moneyOperationCollection->offsetExists(1));
        $this->assertSame([$moneyOperation], iterator_to_array($moneyOperationCollection));

        $this->assertSame($moneyConvert, $moneyConvertCollection->first());
        $this->assertSame($moneyConvert, $moneyConvertCollection->offsetGet(0));
        $this->assertSame([$moneyConvert], $moneyConvertCollection->toArray());
        $this->assertTrue($moneyConvertCollection->offsetExists(0));
        $this->assertFalse($moneyConvertCollection->offsetExists(1));
        $this->assertSame([$moneyConvert], iterator_to_array($moneyConvertCollection));

        $this->assertSame($moneyBalance, $moneyBalanceCollection->first());
        $this->assertSame($moneyBalance, $moneyBalanceCollection->offsetGet(0));
        $this->assertSame([$moneyBalance], $moneyBalanceCollection->toArray());
        $this->assertTrue($moneyBalanceCollection->offsetExists(0));
        $this->assertFalse($moneyBalanceCollection->offsetExists(1));
        $this->assertSame([$moneyBalance], iterator_to_array($moneyBalanceCollection));

        $this->assertSame($stockBalance, $stockBalanceCollection->first());
        $this->assertSame($stockBalance, $stockBalanceCollection->offsetGet(0));
        $this->assertSame([$stockBalance], $stockBalanceCollection->toArray());
        $this->assertTrue($stockBalanceCollection->offsetExists(0));
        $this->assertFalse($stockBalanceCollection->offsetExists(1));
        $this->assertSame([$stockBalance], iterator_to_array($stockBalanceCollection));

        $this->assertSame($stockTransfer, $stockTransferCollection->first());
        $this->assertSame($stockTransfer, $stockTransferCollection->offsetGet(0));
        $this->assertSame([$stockTransfer], $stockTransferCollection->toArray());
        $this->assertTrue($stockTransferCollection->offsetExists(0));
        $this->assertFalse($stockTransferCollection->offsetExists(1));
        $this->assertSame([$stockTransfer], iterator_to_array($stockTransferCollection));

        $this->assertSame($stockPayingOff, $stockPayingOffCollection->first());
        $this->assertSame($stockPayingOff, $stockPayingOffCollection->offsetGet(0));
        $this->assertSame([$stockPayingOff], $stockPayingOffCollection->toArray());
        $this->assertTrue($stockPayingOffCollection->offsetExists(0));
        $this->assertFalse($stockPayingOffCollection->offsetExists(1));
        $this->assertSame([$stockPayingOff], iterator_to_array($stockPayingOffCollection));

        $this->assertSame($corporateAction, $corporateActionCollection->first());
        $this->assertSame($corporateAction, $corporateActionCollection->offsetGet(0));
        $this->assertSame([$corporateAction], $corporateActionCollection->toArray());
        $this->assertTrue($corporateActionCollection->offsetExists(0));
        $this->assertFalse($corporateActionCollection->offsetExists(1));
        $this->assertSame([$corporateAction], iterator_to_array($corporateActionCollection));
    }

    public function testCollectionsReturnNullForFirstWhenEmpty(): void
    {
        $this->assertNull((new RowCollection([]))->first());
        $this->assertNull((new OperIdCollection([]))->first());
        $this->assertNull((new TradeCollection([]))->first());
        $this->assertNull((new MoneyOperationCollection([]))->first());
        $this->assertNull((new MoneyConvertCollection([]))->first());
        $this->assertNull((new MoneyBalanceCollection([]))->first());
        $this->assertNull((new StockBalanceCollection([]))->first());
        $this->assertNull((new StockTransferCollection([]))->first());
        $this->assertNull((new StockPayingOffCollection([]))->first());
        $this->assertNull((new CorporateActionCollection([]))->first());
    }

    public function testReportCanBuildFromRowsAndReturnEmptyDtoCollections(): void
    {
        $report = Report::fromRowsBySection([
            'CommonData' => [],
            'Trades' => [
                new Row('Trades', 'Trades', 'Row', new AttributeBag(['OperID' => '100'])),
                new Row('Trades', 'Trades', 'Row', new AttributeBag(['OperID' => '100'])),
            ],
        ]);

        $this->assertFalse($report->hasSection('CommonData'));
        $this->assertTrue($report->hasSection('Trades'));
        $this->assertSame('100', $report->operIds()->first());
        $this->assertCount(1, $report->operIds());
        $this->assertNotNull($report->findOperId('100'));
        $this->assertNull($report->findOperId('missing'));
        $this->assertNull($report->commonData());
        $this->assertCount(0, $report->moneyInOut());
        $this->assertCount(0, $report->moneyOnDate());
        $this->assertCount(0, $report->stockOnDate());
        $this->assertCount(0, $report->moneyConvert());
        $this->assertCount(0, $report->stockInOut());
        $this->assertCount(0, $report->stockPayingOff());
        $this->assertCount(0, $report->corporateActionsIn());
        $this->assertCount(0, $report->corporateActionsOut());
    }

    public function testReportDtoCollectionsAggregateAcrossCanonicalSections(): void
    {
        $report = Report::fromRowsBySection([
            'Trades' => [
                new Row('Trades', 'TradeCommonSettled', 'TradeCommonSettled', new AttributeBag(['OperID' => '1'])),
            ],
            'TradesRegRepo' => [
                new Row('TradesRegRepo', 'TradeRepoSettled', 'TradeRepoSettled', new AttributeBag(['OperID' => '2'])),
            ],
            'TradesNonRegRepo' => [
                new Row('TradesNonRegRepo', 'TradeRepoNotSettled', 'TradeRepoNotSettled', new AttributeBag(['OperID' => '3'])),
            ],
            'MoneyInOut' => [
                new Row('MoneyInOut', 'OperationMoneyBrok', 'OperationMoneyBrok', new AttributeBag(['OperID' => '4'])),
            ],
            'MoneyInOut_io' => [
                new Row('MoneyInOut_io', 'OperationMoneyInOut', 'OperationMoneyInOut', new AttributeBag(['OperID' => '5'])),
            ],
            'ClientMoneyConvert' => [
                new Row('ClientMoneyConvert', 'TradeFXClient', 'TradeFXClient', new AttributeBag(['OperID' => '6'])),
            ],
            'MoneyConvert' => [
                new Row('MoneyConvert', 'TradeFXNonClient', 'TradeFXNonClient', new AttributeBag(['OperID' => '7'])),
            ],
            'StockOnDate' => [
                new Row('StockOnDate', 'PortfolioStockOTC', 'PortfolioStockOTC', new AttributeBag([])),
            ],
            'StockOnDate_Exg' => [
                new Row('StockOnDate_Exg', 'PortfolioStockEX', 'PortfolioStockEX', new AttributeBag([])),
            ],
            'StockOnDate_NonExg' => [
                new Row('StockOnDate_NonExg', 'PortfolioStockBISLimitedStocks', 'PortfolioStockBISLimitedStocks', new AttributeBag([])),
            ],
            'StockOnDate_MTL' => [
                new Row('StockOnDate_MTL', 'PortfolioStockMetal', 'PortfolioStockMetal', new AttributeBag([])),
            ],
            'StockInOut' => [
                new Row('StockInOut', 'OperationStockInOut', 'OperationStockInOut', new AttributeBag(['OperID' => '8'])),
            ],
            'StockPayingOff' => [
                new Row('StockPayingOff', 'OperationStockPayOff', 'OperationStockPayOff', new AttributeBag(['OperID' => '9'])),
            ],
            'CorpActionIn' => [
                new Row('CorpActionIn', 'OperationStockCorpActionIn', 'OperationStockCorpActionIn', new AttributeBag(['OperID' => '10'])),
            ],
            'CorpActionOut' => [
                new Row('CorpActionOut', 'OperationStockCorpActionOut', 'OperationStockCorpActionOut', new AttributeBag(['OperID' => '11'])),
            ],
        ]);

        $this->assertCount(3, $report->trades());
        $this->assertCount(2, $report->moneyInOut());
        $this->assertCount(2, $report->moneyConvert());
        $this->assertCount(4, $report->stockOnDate());
        $this->assertCount(1, $report->stockInOut());
        $this->assertCount(1, $report->stockPayingOff());
        $this->assertCount(1, $report->corporateActionsIn());
        $this->assertCount(1, $report->corporateActionsOut());
    }

    private function callOffsetGet(object $target, mixed $offset): mixed
    {
        return (new \ReflectionMethod($target, 'offsetGet'))->invoke($target, $offset);
    }
}
