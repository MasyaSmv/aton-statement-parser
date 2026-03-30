<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Parsing\Contracts;

use DOMDocument;
use MasyaSmv\AtonStatementParser\Report\Report;

interface ReportParserInterface
{
    public function supports(DOMDocument $document): bool;

    public function parse(DOMDocument $document): Report;
}
