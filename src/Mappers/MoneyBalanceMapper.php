<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Mappers;

use MasyaSmv\AtonStatementParser\Contracts\Mappers\MoneyBalanceMapperInterface;
use MasyaSmv\AtonStatementParser\Dto\MoneyBalance;
use MasyaSmv\AtonStatementParser\Report\Row;

final class MoneyBalanceMapper implements MoneyBalanceMapperInterface
{
    public function map(Row $row): MoneyBalance
    {
        return new MoneyBalance(
            $row->section(),
            $row->sourceName(),
            $row->getString('AssetCode'),
            $row->getString('Name'),
            $row->getString('Part'),
            $row->getString('PartName'),
            $row->getDecimalString('QtyBeg'),
            $row->getDecimalString('QtyEnd'),
            $row->getDecimalString('QtyBeg_rub'),
            $row->getDecimalString('QtyEnd_rub')
        );
    }
}
