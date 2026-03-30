<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Mappers;

use MasyaSmv\AtonStatementParser\Contracts\Mappers\StockBalanceMapperInterface;
use MasyaSmv\AtonStatementParser\Dto\StockBalance;
use MasyaSmv\AtonStatementParser\Report\Row;

final class StockBalanceMapper implements StockBalanceMapperInterface
{
    public function map(Row $row): StockBalance
    {
        return new StockBalance(
            $row->section(),
            $row->sourceName(),
            $row->getString('AssetCode'),
            $row->getDecimalString('QttyIn'),
            $row->getDecimalString('QttyInAft'),
            $row->getDecimalString('QttyPlan'),
            $row->getDecimalString('QttyOut'),
            $row->getDecimalString('QttyOutAft'),
            $row->getDecimalString('PriceIn'),
            $row->getDecimalString('PriceOut'),
            $row->getString('CurrencyIn'),
            $row->getString('CurrencyOut'),
            $row->getDecimalString('ValueIn'),
            $row->getDecimalString('ValueOut'),
            $row->getDecimalString('NKDIn'),
            $row->getDecimalString('NKDOut')
        );
    }
}
