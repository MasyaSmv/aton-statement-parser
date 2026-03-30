<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Contracts\Mappers;

use MasyaSmv\AtonStatementParser\Dto\StockTransfer;
use MasyaSmv\AtonStatementParser\Report\Row;

interface StockTransferMapperInterface
{
    public function map(Row $row): StockTransfer;
}
