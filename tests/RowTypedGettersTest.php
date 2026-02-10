<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Tests;

use DateTimeImmutable;
use MasyaSmv\AtonStatementParser\AtonStatementParser;
use PHPUnit\Framework\TestCase;

final class RowTypedGettersTest extends TestCase
{
    public function test_row_typed_getters_work(): void
    {
        $path = __DIR__ . '/Fixtures/typed-getters.xml';

        $report = AtonStatementParser::fromFile($path);

        $tradeRow = $report->section('Trades')->rows()[0];

        $this->assertSame('Покупка', $tradeRow->getString('TradeType'));
        $this->assertSame(567890123, $tradeRow->getInt('OperID'));
        $this->assertSame('2.00000000', $tradeRow->getDecimalString('Quantity'));
        $this->assertSame(4099.0, $tradeRow->getFloat('Price')); // float тут ок
        $this->assertSame('-8198.000000', $tradeRow->getDecimalString('Payment'));
        $this->assertFalse($tradeRow->getBool('isComplete'));

        $date1 = $tradeRow->getDate('OperDate');
        $this->assertInstanceOf(DateTimeImmutable::class, $date1);
        $this->assertSame('2024-10-02', $date1->format('Y-m-d'));

        $dateSort = $tradeRow->getDate('OperDateSort');
        $this->assertInstanceOf(DateTimeImmutable::class, $dateSort);
        $this->assertSame('2024-12-25 00:00:00', $dateSort->format('Y-m-d H:i:s'));

        $moneyRow = $report->section('MoneyInOut')->rows()[0];
        $this->assertSame(456789012, $moneyRow->getInt('OperID'));

        $date2 = $moneyRow->getDate('OperDate');
        $this->assertInstanceOf(DateTimeImmutable::class, $date2);
        $this->assertSame('2018-11-16', $date2->format('Y-m-d'));

        $timeSort = $moneyRow->getDate('OperTimeSort');
        $this->assertInstanceOf(DateTimeImmutable::class, $timeSort);
        $this->assertSame('1900-01-01 16:40:30', $timeSort->format('Y-m-d H:i:s'));
    }

    public function test_defaults_are_returned_when_value_is_invalid(): void
    {
        $path = __DIR__ . '/Fixtures/typed-getters.xml';
        $report = AtonStatementParser::fromFile($path);

        $row = $report->section('Trades')->rows()[0];

        $this->assertSame(123, $row->getInt('NoSuchKey', 123));
        $this->assertSame('0.0', $row->getDecimalString('NoSuchKey', '0.0'));
        $this->assertSame(true, $row->getBool('NoSuchKey', true));
    }

    public function test_realistic_aton_dates_are_parsed_correctly(): void
    {
        $path = __DIR__ . '/Fixtures/typed-getters-realistic.xml';

        $report = AtonStatementParser::fromFile($path);

        // Trades: OperDateSort = date at midnight
        $tradeRow = $report->section('Trades')->rows()[0];

        $dateSort = $tradeRow->getDate('OperDateSort');
        $this->assertInstanceOf(DateTimeImmutable::class, $dateSort);
        $this->assertSame('2024-12-25 00:00:00', $dateSort->format('Y-m-d H:i:s'));

        // Trades: OperTimeSort = time with fixed "1900-01-01"
        $timeSort = $tradeRow->getDate('OperTimeSort');
        $this->assertInstanceOf(DateTimeImmutable::class, $timeSort);
        $this->assertSame('1900-01-01 15:51:56', $timeSort->format('Y-m-d H:i:s'));

        // PaymentDate with "/ " мусором
        $paymentDate = $tradeRow->getDate('PaymentDate');
        $this->assertInstanceOf(DateTimeImmutable::class, $paymentDate);
        $this->assertSame('2024-12-26 00:00:00', $paymentDate->format('Y-m-d H:i:s'));

        // MoneyInOut: 2-digit year should become 2018
        $moneyRow = $report->section('MoneyInOut')->rows()[0];

        $operDate = $moneyRow->getDate('OperDate');
        $this->assertInstanceOf(DateTimeImmutable::class, $operDate);
        $this->assertSame('2018-11-16 00:00:00', $operDate->format('Y-m-d H:i:s'));

        $operDateSort = $moneyRow->getDate('OperDateSort');
        $this->assertInstanceOf(DateTimeImmutable::class, $operDateSort);
        $this->assertSame('2018-11-16 00:00:00', $operDateSort->format('Y-m-d H:i:s'));

        $operTimeSort = $moneyRow->getDate('OperTimeSort');
        $this->assertInstanceOf(DateTimeImmutable::class, $operTimeSort);
        $this->assertSame('1900-01-01 16:40:30', $operTimeSort->format('Y-m-d H:i:s'));
    }
}
