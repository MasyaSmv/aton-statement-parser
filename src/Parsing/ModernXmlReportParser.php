<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Parsing;

use DOMDocument;
use DOMElement;
use MasyaSmv\AtonStatementParser\Exceptions\ParseException;
use MasyaSmv\AtonStatementParser\Parsing\Contracts\ReportParserInterface;
use MasyaSmv\AtonStatementParser\Report\AttributeBag;
use MasyaSmv\AtonStatementParser\Report\DiagnosticCollection;
use MasyaSmv\AtonStatementParser\Report\ParseDiagnostic;
use MasyaSmv\AtonStatementParser\Report\Report;
use MasyaSmv\AtonStatementParser\Report\Row;
use MasyaSmv\AtonStatementParser\Support\DecimalStringMath;

final class ModernXmlReportParser implements ReportParserInterface
{
    public function supports(DOMDocument $document): bool
    {
        $root = $document->documentElement;

        return $root instanceof DOMElement && $root->localName === 'root';
    }

    public function parse(DOMDocument $document): Report
    {
        /** @var DOMElement $root */
        $root = $document->documentElement;

        /** @var array<string, list<Row>> $sectionRows */
        $sectionRows = [];
        /** @var list<ParseDiagnostic> $diagnostics */
        $diagnostics = [];

        foreach ($root->childNodes as $sourceElement) {
            if (!$sourceElement instanceof DOMElement || $sourceElement->localName !== 'source') {
                continue;
            }

            $sourceName = trim($sourceElement->getAttribute('name'));

            if ($sourceName === '') {
                throw new ParseException('Source name is missing in modern XML report.');
            }

            if (!KnownModernSchema::isKnownSource($sourceName)) {
                $diagnostics[] = new ParseDiagnostic(
                    'unknown_modern_source',
                    'Unknown modern XML source: ' . $sourceName,
                    'modern',
                    $sourceName
                );
            }

            foreach ($sourceElement->childNodes as $recordElement) {
                if (!$recordElement instanceof DOMElement) {
                    continue;
                }

                $fields = $this->extractFields($recordElement);

                /** @var string $recordType */
                $recordType = $recordElement->localName;

                if (KnownModernSchema::isKnownSource($sourceName) && $recordType !== $sourceName) {
                    $diagnostics[] = new ParseDiagnostic(
                        'unexpected_modern_record_type',
                        'Unexpected modern record type "' . $recordType . '" for source "' . $sourceName . '".',
                        'modern',
                        $sourceName
                    );
                }

                foreach ($this->unexpectedFields($sourceName, $fields) as $fieldName) {
                    $diagnostics[] = new ParseDiagnostic(
                        'unexpected_modern_field',
                        'Unexpected modern XML field "' . $fieldName . '" in source "' . $sourceName . '".',
                        'modern',
                        $sourceName,
                        $fieldName
                    );
                }

                $sectionName = SectionNameResolver::resolveForNewFormat($sourceName, $recordType, $fields);
                $canonicalFields = ModernFieldCanonicalizer::canonicalize(
                    $sourceName,
                    $recordType,
                    $fields
                );

                $sectionRows[$sectionName][] = new Row(
                    $sectionName,
                    $sourceName,
                    $recordType,
                    new AttributeBag($canonicalFields)
                );
            }
        }

        $sectionRows = $this->normalizeSectionRows($sectionRows, $diagnostics);

        return Report::fromRowsBySection($sectionRows, new DiagnosticCollection($diagnostics));
    }

    /** @return array<string, string> */
    private function extractFields(DOMElement $recordElement): array
    {
        $fields = [];

        foreach ($recordElement->childNodes as $fieldElement) {
            if (!$fieldElement instanceof DOMElement) {
                continue;
            }

            /** @var string $fieldName */
            $fieldName = $fieldElement->localName;
            $fields[$fieldName] = trim($fieldElement->textContent);
        }

        return $fields;
    }

    /**
     * @param array<string, list<Row>> $sectionRows
     * @param list<ParseDiagnostic> $diagnostics
     * @return array<string, list<Row>>
     */
    private function normalizeSectionRows(array $sectionRows, array &$diagnostics): array
    {
        if (isset($sectionRows['MoneyInOut_io'])) {
            $sectionRows['MoneyInOut_io'] = $this->collapseDuplicatedMoneyInOutRows($sectionRows['MoneyInOut_io']);
        }

        if (!isset($sectionRows['StockOnDate_Exg_Sum']) && isset($sectionRows['StockOnDate_Exg'])) {
            $derivedRow = $this->deriveStockOnDateExgSumRow($sectionRows['StockOnDate_Exg']);

            if ($derivedRow !== null) {
                $sectionRows['StockOnDate_Exg_Sum'] = [$derivedRow];
                $diagnostics[] = new ParseDiagnostic(
                    'synthetic_legacy_section',
                    'Derived legacy-compatible section "StockOnDate_Exg_Sum" from modern XML source data.',
                    'modern',
                    'StockOnDate_Exg_Sum'
                );
            }
        }

        if (!isset($sectionRows['MoneyOnDate_single']) && isset($sectionRows['MoneyOnDate'])) {
            $sectionRows['MoneyOnDate_single'] = [$this->deriveMoneyOnDateSingleRow()];
            $diagnostics[] = new ParseDiagnostic(
                'synthetic_legacy_section',
                'Derived legacy-compatible section "MoneyOnDate_single" without a direct modern XML source.',
                'modern',
                'MoneyOnDate_single'
            );
        }

        return $sectionRows;
    }

    /**
     * Новый формат иногда содержит точные +/- дубли одной и той же операции
     * для OperationMoneyInOut. В legacy BIS для такого OperID остаётся одна
     * итоговая строка, поэтому схлопываем только строго симметричные пары.
     *
     * @param list<Row> $rows
     * @return list<Row>
     */
    private function collapseDuplicatedMoneyInOutRows(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $grouped[$this->moneyInOutCollapseKey($row)][] = $row;
        }

        $normalized = [];

        foreach ($grouped as $groupRows) {
            if (count($groupRows) === 2 && $this->isExactOppositeMoneyPair($groupRows[0], $groupRows[1])) {
                $normalized[] = $this->preferNegativeMoneyRow($groupRows[0], $groupRows[1]);
                continue;
            }

            foreach ($groupRows as $row) {
                $normalized[] = $row;
            }
        }

        return $normalized;
    }

    private function moneyInOutCollapseKey(Row $row): string
    {
        return implode('|', [
            $row->sourceName(),
            $row->recordType(),
            $row->getString('OperID', ''),
            $row->getString('OperDate', ''),
            $row->getString('PaymentDate', ''),
            $row->getString('Currency', ''),
            $row->getString('Portfolio', ''),
            $row->getString('Comment', ''),
            $this->unsignedDecimal($row->getString('Quantity')),
            $this->unsignedDecimal($row->getString('Quantity_RUR') ?? $row->getString('QuantityRUR')),
        ]);
    }

    private function isExactOppositeMoneyPair(Row $left, Row $right): bool
    {
        $leftQuantity = $left->getString('Quantity');
        $rightQuantity = $right->getString('Quantity');

        if ($leftQuantity === null || $rightQuantity === null) {
            return false;
        }

        return $this->isOppositeDecimalPair($leftQuantity, $rightQuantity)
            && $this->isOppositeDecimalPair(
                $left->getString('Quantity_RUR') ?? $left->getString('QuantityRUR') ?? '0',
                $right->getString('Quantity_RUR') ?? $right->getString('QuantityRUR') ?? '0'
            );
    }

    private function preferNegativeMoneyRow(Row $left, Row $right): Row
    {
        $leftQuantity = $left->getString('Quantity') ?? '';

        return str_starts_with($leftQuantity, '-') ? $left : $right;
    }

    private function isOppositeDecimalPair(string $left, string $right): bool
    {
        return $this->unsignedDecimal($left) === $this->unsignedDecimal($right)
            && str_starts_with($left, '-') !== str_starts_with($right, '-');
    }

    private function unsignedDecimal(?string $value): string
    {
        return ltrim(trim((string) $value), '+-');
    }

    /**
     * @param list<Row> $rows
     */
    private function deriveStockOnDateExgSumRow(array $rows): ?Row
    {
        if ($rows === []) {
            return null;
        }

        $valueIn = '0';
        $valueOut = '0';
        $nkdIn = '0';
        $nkdOut = '0';

        foreach ($rows as $row) {
            $valueIn = DecimalStringMath::add($valueIn, $row->getString('ValueIn') ?? '0');
            $valueOut = DecimalStringMath::add($valueOut, $row->getString('ValueOut') ?? '0');
            $nkdIn = DecimalStringMath::add($nkdIn, $row->getString('NKDIn') ?? '0');
            $nkdOut = DecimalStringMath::add($nkdOut, $row->getString('NKDOut') ?? '0');
        }

        return new Row(
            'StockOnDate_Exg_Sum',
            'DerivedLegacyCompatibility',
            'StockOnDate_Exg_Sum',
            new AttributeBag([
                'ValueIn' => $valueIn,
                'ValueOut' => $valueOut,
                'NKDIn' => $nkdIn,
                'NKDOut' => $nkdOut,
            ])
        );
    }

    private function deriveMoneyOnDateSingleRow(): Row
    {
        return new Row(
            'MoneyOnDate_single',
            'DerivedLegacyCompatibility',
            'MoneyOnDate_single',
            new AttributeBag([
                'ChangeDebtsMZ' => '0.00',
                'MoneyFSIn' => '0.00',
                'MoneyFSOut' => '0.00',
                'MoneyGKOIn' => '0.00',
                'MoneyGKOOut' => '0.00',
                'MoneyGTSGPIn' => '0.00',
                'MoneyGTSGPOut' => '0.00',
                'MicexRenamePAODate' => '19.12.2016',
            ])
        );
    }

    /**
     * @param array<string, string> $fields
     * @return list<string>
     */
    private function unexpectedFields(string $sourceName, array $fields): array
    {
        $allowedFields = KnownModernSchema::allowedFieldsForSource($sourceName);

        if ($allowedFields === null) {
            return [];
        }

        $allowedLookup = array_fill_keys($allowedFields, true);
        $unexpected = [];

        foreach (array_keys($fields) as $fieldName) {
            if (!isset($allowedLookup[$fieldName])) {
                $unexpected[] = $fieldName;
            }
        }

        return $unexpected;
    }
}
