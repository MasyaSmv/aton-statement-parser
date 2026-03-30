<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Contracts\Mappers;

use MasyaSmv\AtonStatementParser\Dto\Trade;
use MasyaSmv\AtonStatementParser\Report\Row;

interface TradeMapperInterface
{
    public function map(Row $row): Trade;
}
