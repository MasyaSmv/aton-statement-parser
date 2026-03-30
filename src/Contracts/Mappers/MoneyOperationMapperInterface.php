<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Contracts\Mappers;

use MasyaSmv\AtonStatementParser\Dto\MoneyOperation;
use MasyaSmv\AtonStatementParser\Report\Row;

interface MoneyOperationMapperInterface
{
    public function map(Row $row): MoneyOperation;
}
