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
use MasyaSmv\AtonStatementParser\Report\DiagnosticCollection;
use MasyaSmv\AtonStatementParser\Report\ParseDiagnostic;
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
        /** @var list<ParseDiagnostic> $diagnostics */
        $diagnostics = [];

        foreach ($period->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            /** @var string $sectionName */
            $sectionName = $child->localName;
            /** @var string $namespace */
            $namespace = $period->namespaceURI;

            if (!KnownLegacySchema::isKnownSection($sectionName)) {
                $diagnostics[] = new ParseDiagnostic(
                    'unknown_legacy_section',
                    'Unknown legacy BIS section: ' . $sectionName,
                    'legacy',
                    $sectionName
                );
            }

            foreach ($child->getElementsByTagNameNS($namespace, 'Row') as $rowElement) {
                $attributes = $this->extractAttributes($rowElement);

                foreach ($this->unexpectedFields($sectionName, $attributes) as $fieldName) {
                    $diagnostics[] = new ParseDiagnostic(
                        'unexpected_legacy_field',
                        'Unexpected legacy BIS field "' . $fieldName . '" in section "' . $sectionName . '".',
                        'legacy',
                        $sectionName,
                        $fieldName
                    );
                }

                $sectionRows[$sectionName][] = new Row(
                    $sectionName,
                    $sectionName,
                    'Row',
                    new AttributeBag($attributes)
                );
            }
        }

        return Report::fromRowsBySection($sectionRows, new DiagnosticCollection($diagnostics));
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

    /**
     * @param array<string, string> $attributes
     * @return list<string>
     */
    private function unexpectedFields(string $sectionName, array $attributes): array
    {
        $allowedFields = KnownLegacySchema::allowedFieldsForSection($sectionName);

        if ($allowedFields === null) {
            return [];
        }

        $allowedLookup = array_fill_keys($allowedFields, true);
        $unexpected = [];

        foreach (array_keys($attributes) as $fieldName) {
            if (!isset($allowedLookup[$fieldName])) {
                $unexpected[] = $fieldName;
            }
        }

        return $unexpected;
    }
}
