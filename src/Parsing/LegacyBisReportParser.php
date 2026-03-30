<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Parsing;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMXPath;
use MasyaSmv\AtonStatementParser\Exceptions\ParseException;
use MasyaSmv\AtonStatementParser\Parsing\Contracts\ReportParserInterface;
use MasyaSmv\AtonStatementParser\Report\AttributeBag;
use MasyaSmv\AtonStatementParser\Report\Report;
use MasyaSmv\AtonStatementParser\Report\Row;
use MasyaSmv\AtonStatementParser\Xml\XPathFactory;

final class LegacyBisReportParser implements ReportParserInterface
{
    public function supports(DOMDocument $document): bool
    {
        $root = $document->documentElement;

        return $root instanceof DOMElement && $root->localName === 'BISPeriod';
    }

    public function parse(DOMDocument $document): Report
    {
        $xpath = XPathFactory::make($document);
        $period = $this->resolvePeriod($xpath);

        /** @var array<string, list<Row>> $sectionRows */
        $sectionRows = [];

        foreach ($period->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            /** @var string $sectionName */
            $sectionName = $child->localName;
            /** @var string $namespace */
            $namespace = $period->namespaceURI;

            foreach ($child->getElementsByTagNameNS($namespace, 'Row') as $rowElement) {
                $sectionRows[$sectionName][] = new Row(
                    $sectionName,
                    $sectionName,
                    'Row',
                    new AttributeBag($this->extractAttributes($rowElement))
                );
            }
        }

        return Report::fromRowsBySection($sectionRows);
    }

    private function resolvePeriod(DOMXPath $xpath): DOMElement
    {
        $periodNodes = $xpath->query('/BIS:BISPeriod');

        if ($periodNodes === false || $periodNodes->length === 0) {
            throw new ParseException('Root node BIS:BISPeriod not found.');
        }

        /** @var DOMElement $period */
        $period = $periodNodes->item(0);

        return $period;
    }

    /** @return array<string, string> */
    private function extractAttributes(DOMElement $rowElement): array
    {
        $attributes = [];

        foreach ($rowElement->attributes as $attribute) {
            /** @var DOMAttr $attribute */
            /** @var string $key */
            $key = $attribute->localName;
            $attributes[$key] = $attribute->value;
        }

        return $attributes;
    }
}
