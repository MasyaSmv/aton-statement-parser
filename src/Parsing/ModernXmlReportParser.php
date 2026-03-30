<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Parsing;

use DOMDocument;
use DOMElement;
use MasyaSmv\AtonStatementParser\Exceptions\ParseException;
use MasyaSmv\AtonStatementParser\Parsing\Contracts\ReportParserInterface;
use MasyaSmv\AtonStatementParser\Report\AttributeBag;
use MasyaSmv\AtonStatementParser\Report\Report;
use MasyaSmv\AtonStatementParser\Report\Row;

final class ModernXmlReportParser implements ReportParserInterface
{
    public function supports(DOMDocument $document): bool
    {
        $root = $document->documentElement;

        return $root instanceof DOMElement && $root->localName === 'root';
    }

    public function parse(DOMDocument $document): Report
    {
        $root = $document->documentElement;

        if (!$root instanceof DOMElement) {
            throw new ParseException('Root element not found for modern XML report.');
        }

        /** @var array<string, list<Row>> $sectionRows */
        $sectionRows = [];

        foreach ($root->childNodes as $sourceElement) {
            if (!$sourceElement instanceof DOMElement || $sourceElement->localName !== 'source') {
                continue;
            }

            $sourceName = trim($sourceElement->getAttribute('name'));
            if ($sourceName === '') {
                throw new ParseException('Source name is missing in modern XML report.');
            }

            foreach ($sourceElement->childNodes as $recordElement) {
                if (!$recordElement instanceof DOMElement) {
                    continue;
                }

                $recordType = $recordElement->localName ?? '';
                $sectionName = SectionNameResolver::resolveForNewFormat($sourceName, $recordType);
                $fields = ModernFieldCanonicalizer::canonicalize(
                    $sourceName,
                    $recordType,
                    $this->extractFields($recordElement)
                );

                $sectionRows[$sectionName][] = new Row(
                    $sectionName,
                    $sourceName,
                    $recordType,
                    new AttributeBag($fields)
                );
            }
        }

        return Report::fromRowsBySection($sectionRows);
    }

    /** @return array<string, string> */
    private function extractFields(DOMElement $recordElement): array
    {
        $fields = [];

        foreach ($recordElement->childNodes as $fieldElement) {
            if (!$fieldElement instanceof DOMElement) {
                continue;
            }

            $fieldName = $fieldElement->localName ?? '';
            if ($fieldName === '') {
                continue;
            }

            $fields[$fieldName] = trim($fieldElement->textContent);
        }

        return $fields;
    }
}
