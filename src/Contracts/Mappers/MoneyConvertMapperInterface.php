<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Contracts\Mappers;

use MasyaSmv\AtonStatementParser\Dto\MoneyConvertOperation;
use MasyaSmv\AtonStatementParser\Report\Row;

interface MoneyConvertMapperInterface
{
    public function map(Row $row): MoneyConvertOperation;
}
