<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Parsing;

use DOMDocument;
use MasyaSmv\AtonStatementParser\Exceptions\UnsupportedReportFormatException;
use MasyaSmv\AtonStatementParser\Parsing\Contracts\ReportParserInterface;
use MasyaSmv\AtonStatementParser\Report\Report;

final class ReportParserResolver
{
    /** @var list<ReportParserInterface> */
    private array $parsers;

    public function __construct()
    {
        $this->parsers = [
            new LegacyBisReportParser(),
            new ModernXmlReportParser(),
        ];
    }

    public function parse(DOMDocument $document): Report
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($document)) {
                return $parser->parse($document);
            }
        }

        $rootName = $document->documentElement?->tagName ?? '[unknown]';

        throw new UnsupportedReportFormatException('Unsupported XML report format. Root element: ' . $rootName);
    }
}
