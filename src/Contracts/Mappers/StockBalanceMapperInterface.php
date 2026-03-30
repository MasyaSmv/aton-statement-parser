<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Contracts\Mappers;

use MasyaSmv\AtonStatementParser\Dto\StockBalance;
use MasyaSmv\AtonStatementParser\Report\Row;

interface StockBalanceMapperInterface
{
    public function map(Row $row): StockBalance;
}
