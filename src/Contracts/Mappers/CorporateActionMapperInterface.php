<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Contracts\Mappers;

use MasyaSmv\AtonStatementParser\Dto\CorporateAction;
use MasyaSmv\AtonStatementParser\Report\Row;

interface CorporateActionMapperInterface
{
    public function map(Row $row): CorporateAction;
}
