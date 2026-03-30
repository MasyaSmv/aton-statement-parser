<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Contracts\Mappers;

use MasyaSmv\AtonStatementParser\Dto\CommonData;
use MasyaSmv\AtonStatementParser\Report\Row;

interface CommonDataMapperInterface
{
    public function map(Row $row): CommonData;
}
