<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Contracts;

use MasyaSmv\AtonStatementParser\Report\Row;
use MasyaSmv\AtonStatementParser\Report\Section;

interface ReportInterface
{
    public function hasSection(string $name): bool;

    public function section(string $name): Section;

    /** @return array<int, string> */
    public function operIds(): array;

    public function findOperId(string $operId): ?Row;
}
