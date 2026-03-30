<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Parsing;

final class ModernFieldCanonicalizer
{
    /**
     * @param array<string, string> $fields
     * @return array<string, string>
     */
    public static function canonicalize(string $sourceName, string $recordType, array $fields): array
    {
        $canonical = $fields;

        self::canonicalizeHeader($sourceName, $canonical);
        self::canonicalizeTrade($sourceName, $canonical);
        self::canonicalizeRepoTrade($sourceName, $canonical);
        self::canonicalizeFxTrade($sourceName, $canonical);
        self::canonicalizeMoneyOperation($sourceName, $canonical);
        self::canonicalizePortfolioMoney($sourceName, $canonical);
        self::canonicalizePortfolioStock($sourceName, $canonical);
        self::canonicalizeStockOperation($sourceName, $canonical);

        return $canonical;
    }

    /** @param array<string, string> $fields */
    private static function canonicalizeHeader(string $sourceName, array &$fields): void
    {
        if ($sourceName !== 'Header') {
            return;
        }

        self::copy($fields, 'CpID', 'CPID');
        self::copyDate($fields, 'DateBegin', 'BegDate');
        self::copyDate($fields, 'DateEnd', 'EndDate');
        self::copyDate($fields, 'ReportDate', 'MakeDate');
        self::copyBool($fields, 'WithSubaccount', 'WithSubAccounts');
        self::copyBool($fields, 'IsFilial', 'IsFilial');
    }

    /** @param array<string, string> $fields */
    private static function canonicalizeTrade(string $sourceName, array &$fields): void
    {
        if ($sourceName !== 'TradeCommonSettled' && $sourceName !== 'TradeCommonNotSettled') {
            return;
        }

        $isComplete = $sourceName === 'TradeCommonSettled' ? '1' : '0';
        $fields['isComplete'] ??= $isComplete;

        if (!isset($fields['OperDateSort']) || !isset($fields['OperTimeSort'])) {
            $dateTime = self::extractDateTimeFromIntOperNum($fields['IntOperNum'] ?? null);

            if ($dateTime !== null) {
                [$date, $time] = $dateTime;
                $fields['OperDateSort'] ??= self::expandShortDate($date) . ' 0:00:00';
                $fields['OperTimeSort'] ??= '01.01.1900 ' . $time;
            } else {
                $fallbackDate = self::extractFirstShortDate(
                    $fields['PaymentDate'] ?? $fields['SettlementDate'] ?? $fields['ExSettlementDate'] ?? ''
                );

                if ($fallbackDate !== null) {
                    $fields['OperDateSort'] ??= self::expandShortDate($fallbackDate) . ' 0:00:00';
                }

                $fields['OperTimeSort'] ??= '01.01.1900 0:00:00';
            }
        }

        $fields['isRPS'] ??= '0';
        $fields['IsOperWithoutPrice'] ??= '0';
        $fields['IsPif'] ??= '0';
        $fields['IsIB'] ??= '0';
        $fields['ComDlrCurrency'] ??= '';
        $fields['IntOperNum'] ??= '';
    }

    /** @param array<string, string> $fields */
    private static function canonicalizeRepoTrade(string $sourceName, array &$fields): void
    {
        if ($sourceName !== 'TradeRepoSettled' && $sourceName !== 'TradeRepoNotSettled') {
            return;
        }

        $isComplete = $sourceName === 'TradeRepoSettled' ? '1' : '0';
        $fields['isComplete'] ??= $isComplete;

        if (!isset($fields['OperDateSort']) || !isset($fields['OperTimeSort'])) {
            $dateTime = self::extractDateTimeFromIntOperNum($fields['IntOperNum'] ?? null);

            if ($dateTime !== null) {
                [$date, $time] = $dateTime;
                $fields['OperDateSort'] ??= self::expandShortDate($date) . ' 0:00:00';
                $fields['OperTimeSort'] ??= '01.01.1900 ' . $time;
            }
        }

        $fields['isRPS'] ??= '0';
        $fields['RepoDiscount'] ??= '';
        $fields['RepoPeriod'] ??= '';
        $fields['LinkOperID'] ??= '';
        $fields['SortRepo'] ??= '';
        $fields['ExSettlementDate'] ??= '';
        $fields['ComDlrCurrency'] ??= '';

        if ($sourceName === 'TradeRepoSettled') {
            $fields['RepoMarginCall'] ??= '';
        }
    }

    /** @param array<string, string> $fields */
    private static function canonicalizeFxTrade(string $sourceName, array &$fields): void
    {
        if ($sourceName !== 'TradeFXClient' && $sourceName !== 'TradeFXNonClient') {
            return;
        }

        $operDate = self::expandShortDate(self::normalizeDateLikeValue($fields['Date1'] ?? ''));

        if ($operDate !== '') {
            $fields['OperDate'] ??= $operDate;
            $fields['OperDateSort'] ??= $operDate . ' 0:00:00';
        }

        self::copy($fields, 'Sum1RUR', 'Sum1_RUR');
        self::copy($fields, 'Sum2RUR', 'Sum2_RUR');
        $fields['OperTime'] ??= '01.01.1900 00:00:00';
        $fields['Section'] ??= $sourceName === 'TradeFXClient' ? 'ClientMoneyConvert' : 'MoneyConvert';
    }

    /** @param array<string, string> $fields */
    private static function canonicalizeMoneyOperation(string $sourceName, array &$fields): void
    {
        $typeMap = [
            'OperationMoneyBrok' => ['Brok', '30'],
            'OperationMoneyDepo' => ['Depo', '25'],
            'OperationMoneyInOut' => ['InOut', '10'],
        ];

        if (!isset($typeMap[$sourceName])) {
            return;
        }

        [$operType, $operTypeSort] = $typeMap[$sourceName];
        $fields['OperType'] ??= $operType;
        $fields['OperTypeSort'] ??= $operTypeSort;
        $fields['OperDateSort'] ??= self::expandShortDate(($fields['OperDate'] ?? '')) . ' 0:00:00';
        $fields['OperTimeSort'] ??= '01.01.1900 0:00:00';
        $fields['PaymentDate'] ??= '';
        $fields['SettlementDate'] ??= $fields['OperDate'] ?? '';
        $fields['IsIBStock'] ??= '0';

        if (isset($fields['QuantityRUR'])) {
            $fields['Quantity_RUR'] ??= $fields['QuantityRUR'];
        }

        if ($sourceName === 'OperationMoneyInOut' && !isset($fields['Quantity_RUR']) && isset($fields['Quantity'])) {
            $fields['Quantity_RUR'] = $fields['Quantity'];
        }

        if ($sourceName === 'OperationMoneyInOut') {
            $fields['IntOperType'] ??= '';
        }
    }

    /** @param array<string, string> $fields */
    private static function canonicalizePortfolioMoney(string $sourceName, array &$fields): void
    {
        if ($sourceName !== 'PortfolioMoney') {
            return;
        }

        self::copy($fields, 'QuantityBegin', 'QtyBeg');
        self::copy($fields, 'QuantityEnd', 'QtyEnd');
        self::copy($fields, 'QuantityBeginRUR', 'QtyBeg_rub');
        self::copy($fields, 'QuantityEndRUR', 'QtyEnd_rub');
        self::copy($fields, 'PartOrder', 'n1');
        self::copy($fields, 'RecordOrder', 'n2');
        self::copy($fields, 'SectionName', 'Name');
    }

    /** @param array<string, string> $fields */
    private static function canonicalizePortfolioStock(string $sourceName, array &$fields): void
    {
        if (
            $sourceName !== 'PortfolioStockEX'
            && $sourceName !== 'PortfolioStockOTC'
            && $sourceName !== 'PortfolioStockBISLimitedStocks'
            && $sourceName !== 'PortfolioStockMetal'
        ) {
            return;
        }

        self::copy($fields, 'QuantityBegin', 'QttyIn');
        self::copy($fields, 'QuantityAvailableBegin', 'QttyInAft');
        self::copy($fields, 'QuantitytWillBe', 'QttyPlan');
        self::copy($fields, 'QuantityEnd', 'QttyOut');
        self::copy($fields, 'QuantityAvailableEnd', 'QttyOutAft');
        self::copy($fields, 'PriceBegin', 'PriceIn');
        self::copy($fields, 'PriceEnd', 'PriceOut');
        self::copy($fields, 'AmountBegin', 'ValueIn');
        self::copy($fields, 'AmountEnd', 'ValueOut');
        self::copy($fields, 'NKDBegin', 'NKDIn');
        self::copy($fields, 'NKDEnd', 'NKDOut');
        self::copy($fields, 'PriceCurrBegin', 'CurrencyIn');
        self::copy($fields, 'PriceCurrEnd', 'CurrencyOut');
        $fields['OperPlace'] ??= '1';
    }

    /** @param array<string, string> $fields */
    private static function canonicalizeStockOperation(string $sourceName, array &$fields): void
    {
        if (!str_starts_with($sourceName, 'OperationStock')) {
            return;
        }

        $fields['OperDateSort'] ??= self::expandShortDate(($fields['OperDate'] ?? '')) . ' 0:00:00';
        $fields['OperTimeSort'] ??= '01.01.1900 0:00:00';

        if (str_contains($sourceName, 'CorpAction')) {
            $fields['GroupID'] ??= 'Stk';
            $fields['NominalCurr'] ??= '';
            $fields['PayingSum'] ??= '0.00000000';
            $fields['PayingSum_RUR'] ??= '0.000000';
        }

        if ($sourceName === 'OperationStockInOut') {
            $fields['Price'] ??= '';
            $fields['Section'] ??= 'StockInOut';
        }

        if ($sourceName === 'OperationStockPayOff') {
            $fields['Price'] ??= '';
            $fields['Section'] ??= 'StockPayingOff';
            $fields['GroupID'] ??= 'Stk';

            if (isset($fields['PayingSumRUR'])) {
                $fields['PayingSum_RUR'] ??= $fields['PayingSumRUR'];
            }
            $fields['IntOperType'] ??= 'MTRT';
        }
    }

    /** @param array<string, string> $fields */
    private static function copy(array &$fields, string $from, string $to): void
    {
        if (isset($fields[$from]) && !isset($fields[$to])) {
            $fields[$to] = $fields[$from];
        }
    }

    /** @param array<string, string> $fields */
    private static function copyBool(array &$fields, string $from, string $to): void
    {
        if (!isset($fields[$from]) || isset($fields[$to])) {
            return;
        }

        $value = strtolower(trim($fields[$from]));
        $fields[$to] = $value === 'true' ? '1' : '0';
    }

    /** @param array<string, string> $fields */
    private static function copyDate(array &$fields, string $from, string $to): void
    {
        if (!isset($fields[$from]) || isset($fields[$to])) {
            return;
        }

        $fields[$to] = self::normalizeIsoDateToDotFormat($fields[$from]);
    }

    private static function normalizeIsoDateToDotFormat(string $value): string
    {
        $date = substr(trim($value), 0, 10);
        $parts = explode('-', $date);

        if (count($parts) !== 3) {
            return $value;
        }

        return sprintf('%s.%s.%s', $parts[2], $parts[1], $parts[0]);
    }

    private static function expandShortDate(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $value)) {
            return $value;
        }

        if (preg_match('/^(\d{2})\.(\d{2})\.(\d{2})$/', $value, $matches)) {
            $year = (int) $matches[3];
            $fullYear = $year <= 69 ? 2000 + $year : 1900 + $year;

            return sprintf('%s.%s.%04d', $matches[1], $matches[2], $fullYear);
        }

        return $value;
    }

    private static function normalizeDateLikeValue(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value)) {
            return $value;
        }

        if (preg_match('/^\d{2}\.\d{2}\.\d{2}$/', $value)) {
            return $value;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return self::normalizeIsoDateToDotFormat($value);
        }

        return $value;
    }

    private static function extractFirstShortDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        if (preg_match('/(\d{2}\.\d{2}\.\d{2,4})/', $value, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /** @return array{0: string, 1: string}|null */
    private static function extractDateTimeFromIntOperNum(?string $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!preg_match('/,\s*(\d{2}\.\d{2}\.\d{2}),\s*(\d{2}:\d{2}:\d{2})/', $value, $matches)) {
            return null;
        }

        return [$matches[1], $matches[2]];
    }
}
