<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Contracts\Mappers;

use MasyaSmv\AtonStatementParser\Dto\StockPayingOff;
use MasyaSmv\AtonStatementParser\Report\Row;

interface StockPayingOffMapperInterface
{
    public function map(Row $row): StockPayingOff;
}
