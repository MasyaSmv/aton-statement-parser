<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Report;

use MasyaSmv\AtonStatementParser\Collections\RowCollection;

final class Section
{
    public function __construct(
        private string $name,
        private RowCollection $rows
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function rows(): RowCollection
    {
        return $this->rows;
    }
}
