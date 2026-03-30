<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Tests;

use LogicException;
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
use MasyaSmv\AtonStatementParser\Report\DiagnosticCollection;
use MasyaSmv\AtonStatementParser\Report\ParseDiagnostic;
use MasyaSmv\AtonStatementParser\Report\Row;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

final class ImmutableCollectionsTest extends TestCase
{
    public function testAttributeBagIsImmutable(): void
    {
        $bag = new AttributeBag(['A' => '1']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('AttributeBag is immutable');

        $bag['A'] = '2';
    }

    public function testAttributeBagOffsetUnsetThrows(): void
    {
        $bag = new AttributeBag(['A' => '1']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('AttributeBag is immutable');

        unset($bag['A']);
    }

    public function testAttributeBagReturnsNullForInvalidOffsetType(): void
    {
        $bag = new AttributeBag(['A' => '1']);

        self::assertNull($this->callOffsetGet($bag, 1));
        self::assertFalse($this->callOffsetExists($bag, 1));
    }

    public function testRowCollectionThrowsOnInvalidIndexAndMutation(): void
    {
        $row = new Row('Trades', 'Trades', 'Row', new AttributeBag(['OperID' => '1']));
        $collection = new RowCollection([$row]);

        try {
            $collection->get(10);
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertStringContainsString('Row index out of bounds', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('RowCollection is immutable');

        $collection[] = $row;
    }

    public function testRowCollectionThrowsOnInvalidOffsetTypeAndUnset(): void
    {
        $row = new Row('Trades', 'Trades', 'Row', new AttributeBag(['OperID' => '1']));
        $collection = new RowCollection([$row]);

        try {
            $this->callOffsetGet($collection, 'bad');
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertSame('Row index must be integer.', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('RowCollection is immutable');

        unset($collection[0]);
    }

    public function testOperIdCollectionThrowsOnInvalidIndexAndMutation(): void
    {
        $collection = new OperIdCollection(['1']);

        try {
            $collection->get(10);
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertStringContainsString('OperID index out of bounds', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('OperIdCollection is immutable');

        $collection[] = '2';
    }

    public function testOperIdCollectionThrowsOnInvalidOffsetTypeAndUnset(): void
    {
        $collection = new OperIdCollection(['1']);

        try {
            $this->callOffsetGet($collection, 'bad');
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertSame('OperID index must be integer.', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('OperIdCollection is immutable');

        unset($collection[0]);
    }

    public function testTradeCollectionThrowsOnOutOfBoundsAndMutation(): void
    {
        $collection = new TradeCollection([$this->makeTrade()]);

        try {
            $collection->get(10);
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertStringContainsString('Trade index out of bounds', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('TradeCollection is immutable');

        $collection[] = $this->makeTrade();
    }

    public function testTradeCollectionThrowsOnInvalidOffsetTypeAndUnset(): void
    {
        $collection = new TradeCollection([$this->makeTrade()]);

        try {
            $this->callOffsetGet($collection, 'bad');
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertSame('Trade index must be integer.', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('TradeCollection is immutable');

        unset($collection[0]);
    }

    public function testMoneyOperationCollectionThrowsOnOutOfBoundsAndMutation(): void
    {
        $collection = new MoneyOperationCollection([$this->makeMoneyOperation()]);

        try {
            $collection->get(10);
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertStringContainsString('MoneyOperation index out of bounds', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('MoneyOperationCollection is immutable');

        $collection[] = $this->makeMoneyOperation();
    }

    public function testMoneyOperationCollectionThrowsOnInvalidOffsetTypeAndUnset(): void
    {
        $collection = new MoneyOperationCollection([$this->makeMoneyOperation()]);

        try {
            $this->callOffsetGet($collection, 'bad');
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertSame('MoneyOperation index must be integer.', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('MoneyOperationCollection is immutable');

        unset($collection[0]);
    }

    public function testMoneyConvertCollectionThrowsOnOutOfBoundsAndMutation(): void
    {
        $collection = new MoneyConvertCollection([$this->makeMoneyConvert()]);

        try {
            $collection->get(10);
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertStringContainsString('MoneyConvertOperation index out of bounds', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('MoneyConvertCollection is immutable');

        $collection[] = $this->makeMoneyConvert();
    }

    public function testMoneyConvertCollectionThrowsOnInvalidOffsetTypeAndUnset(): void
    {
        $collection = new MoneyConvertCollection([$this->makeMoneyConvert()]);

        try {
            $this->callOffsetGet($collection, 'bad');
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertSame('MoneyConvertOperation index must be integer.', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('MoneyConvertCollection is immutable');

        unset($collection[0]);
    }

    public function testMoneyBalanceCollectionThrowsOnOutOfBoundsAndMutation(): void
    {
        $collection = new MoneyBalanceCollection([$this->makeMoneyBalance()]);

        try {
            $collection->get(10);
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertStringContainsString('MoneyBalance index out of bounds', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('MoneyBalanceCollection is immutable');

        $collection[] = $this->makeMoneyBalance();
    }

    public function testMoneyBalanceCollectionThrowsOnInvalidOffsetTypeAndUnset(): void
    {
        $collection = new MoneyBalanceCollection([$this->makeMoneyBalance()]);

        try {
            $this->callOffsetGet($collection, 'bad');
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertSame('MoneyBalance index must be integer.', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('MoneyBalanceCollection is immutable');

        unset($collection[0]);
    }

    public function testStockBalanceCollectionThrowsOnOutOfBoundsAndMutation(): void
    {
        $collection = new StockBalanceCollection([$this->makeStockBalance()]);

        try {
            $collection->get(10);
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertStringContainsString('StockBalance index out of bounds', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('StockBalanceCollection is immutable');

        $collection[] = $this->makeStockBalance();
    }

    public function testStockBalanceCollectionThrowsOnInvalidOffsetTypeAndUnset(): void
    {
        $collection = new StockBalanceCollection([$this->makeStockBalance()]);

        try {
            $this->callOffsetGet($collection, 'bad');
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertSame('StockBalance index must be integer.', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('StockBalanceCollection is immutable');

        unset($collection[0]);
    }

    public function testStockTransferCollectionThrowsOnOutOfBoundsAndMutation(): void
    {
        $collection = new StockTransferCollection([$this->makeStockTransfer()]);

        try {
            $collection->get(10);
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertStringContainsString('StockTransfer index out of bounds', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('StockTransferCollection is immutable');

        $collection[] = $this->makeStockTransfer();
    }

    public function testStockTransferCollectionThrowsOnInvalidOffsetTypeAndUnset(): void
    {
        $collection = new StockTransferCollection([$this->makeStockTransfer()]);

        try {
            $this->callOffsetGet($collection, 'bad');
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertSame('StockTransfer index must be integer.', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('StockTransferCollection is immutable');

        unset($collection[0]);
    }

    public function testStockPayingOffCollectionThrowsOnOutOfBoundsAndMutation(): void
    {
        $collection = new StockPayingOffCollection([$this->makeStockPayingOff()]);

        try {
            $collection->get(10);
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertStringContainsString('StockPayingOff index out of bounds', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('StockPayingOffCollection is immutable');

        $collection[] = $this->makeStockPayingOff();
    }

    public function testStockPayingOffCollectionThrowsOnInvalidOffsetTypeAndUnset(): void
    {
        $collection = new StockPayingOffCollection([$this->makeStockPayingOff()]);

        try {
            $this->callOffsetGet($collection, 'bad');
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertSame('StockPayingOff index must be integer.', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('StockPayingOffCollection is immutable');

        unset($collection[0]);
    }

    public function testCorporateActionCollectionThrowsOnOutOfBoundsAndMutation(): void
    {
        $collection = new CorporateActionCollection([$this->makeCorporateAction()]);

        try {
            $collection->get(10);
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertStringContainsString('CorporateAction index out of bounds', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('CorporateActionCollection is immutable');

        $collection[] = $this->makeCorporateAction();
    }

    public function testCorporateActionCollectionThrowsOnInvalidOffsetTypeAndUnset(): void
    {
        $collection = new CorporateActionCollection([$this->makeCorporateAction()]);

        try {
            $this->callOffsetGet($collection, 'bad');
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertSame('CorporateAction index must be integer.', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('CorporateActionCollection is immutable');

        unset($collection[0]);
    }

    public function testDiagnosticCollectionThrowsOnInvalidOffsetTypeAndUnset(): void
    {
        $collection = new DiagnosticCollection([new ParseDiagnostic('code', 'message', 'modern')]);

        try {
            $this->callOffsetGet($collection, 'bad');
            $this->fail('Expected exception was not thrown.');
        } catch (OutOfBoundsException $exception) {
            $this->assertSame('Diagnostic index must be an integer.', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('DiagnosticCollection is immutable.');

        unset($collection[0]);
    }

    public function testDiagnosticCollectionThrowsOnMutation(): void
    {
        $collection = new DiagnosticCollection([new ParseDiagnostic('code', 'message', 'modern')]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('DiagnosticCollection is immutable.');

        $collection[] = new ParseDiagnostic('other', 'message', 'legacy');
    }

    private function makeTrade(): Trade
    {
        return new Trade('1', 'Trades', 'Trades', null, null, null, null, null, null, null, null, null, null, null, null);
    }

    private function makeMoneyOperation(): MoneyOperation
    {
        return new MoneyOperation('1', 'MoneyInOut', 'MoneyInOut', null, null, null, null, null, null, null, null);
    }

    private function makeMoneyConvert(): MoneyConvertOperation
    {
        return new MoneyConvertOperation('1', 'MoneyConvert', 'TradeFXNonClient', null, null, null, null, null, null, null, null, null, null);
    }

    private function makeMoneyBalance(): MoneyBalance
    {
        return new MoneyBalance('MoneyOnDate', 'PortfolioMoney', null, null, null, null, null, null, null, null);
    }

    private function makeStockBalance(): StockBalance
    {
        return new StockBalance('StockOnDate', 'PortfolioStockOTC', null, null, null, null, null, null, null, null, null, null, null, null, null, null);
    }

    private function makeStockTransfer(): StockTransfer
    {
        return new StockTransfer('1', 'StockInOut', 'OperationStockInOut', null, null, null, null, null, null, null, null, null, null, null);
    }

    private function makeStockPayingOff(): StockPayingOff
    {
        return new StockPayingOff('1', 'StockPayingOff', 'OperationStockPayOff', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
    }

    private function makeCorporateAction(): CorporateAction
    {
        return new CorporateAction('1', 'CorpActionIn', 'OperationStockCorpActionIn', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
    }

    private function callOffsetGet(object $target, mixed $offset): mixed
    {
        return (new \ReflectionMethod($target, 'offsetGet'))->invoke($target, $offset);
    }

    private function callOffsetExists(object $target, mixed $offset): bool
    {
        return (bool) (new \ReflectionMethod($target, 'offsetExists'))->invoke($target, $offset);
    }
}
