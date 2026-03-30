<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Parsing;

final class SectionNameResolver
{
    /**
     * @param array<string, string> $fields
     */
    public static function resolveForNewFormat(string $sourceName, string $recordType, array $fields = []): string
    {
        return match (true) {
            $sourceName === 'Header' => 'CommonData',
            $sourceName === 'TradeCommonSettled',
            $sourceName === 'TradeCommonNotSettled' => 'Trades',
            $sourceName === 'TradeFXClient' => 'ClientMoneyConvert',
            $sourceName === 'TradeFXNonClient' => 'MoneyConvert',
            $sourceName === 'TradeRepoSettled' => 'TradesRegRepo',
            $sourceName === 'TradeRepoNotSettled' => 'TradesNonRegRepo',
            $sourceName === 'OperationMoneyBrok',
            $sourceName === 'OperationMoneyDepo' => 'MoneyInOut',
            $sourceName === 'OperationMoneyInOut' => 'MoneyInOut_io',
            $sourceName === 'OperationStockCorpActionIn' => 'CorpActionIn',
            $sourceName === 'OperationStockCorpActionOut' => 'CorpActionOut',
            $sourceName === 'OperationStockInOut' => 'StockInOut',
            $sourceName === 'OperationStockPayOff' => 'StockPayingOff',
            $sourceName === 'PortfolioMoney' => self::resolvePortfolioMoneySection($fields),
            $sourceName === 'PortfolioStockEX' => 'StockOnDate_Exg',
            $sourceName === 'PortfolioStockOTC' => 'StockOnDate',
            $sourceName === 'PortfolioStockBISLimitedStocks' => 'StockOnDate_NonExg',
            $sourceName === 'PortfolioStockMetal' => 'StockOnDate_MTL',
            default => $recordType !== '' ? $recordType : $sourceName,
        };
    }

    /**
     * @param array<string, string> $fields
     */
    private static function resolvePortfolioMoneySection(array $fields): string
    {
        $section = $fields['Section'] ?? '';

        return match (true) {
            str_starts_with($section, '1_PortfolioMoney_Value') => 'MoneyOnDate_MarketPrc',
            str_starts_with($section, '4_PortfolioMoney_ByOperPlace') => 'MoneyOnDate_ByOperPlace',
            default => 'MoneyOnDate',
        };
    }
}
