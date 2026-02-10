<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Report;

final class Section
{
    /** @param Row[] $rows */
    public function __construct(
        private string $name,
        private array $rows
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    /** @return Row[] */
    public function rows(): array
    {
        return $this->rows;
    }
}
