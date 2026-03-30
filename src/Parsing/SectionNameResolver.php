<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Parsing;

final class SectionNameResolver
{
    public static function resolveForNewFormat(string $sourceName, string $recordType): string
    {
        return match (true) {
            $sourceName === 'Header' => 'CommonData',
            $sourceName === 'TradeCommonSettled',
            $sourceName === 'TradeCommonNotSettled' => 'Trades',
            $sourceName === 'OperationMoneyBrok',
            $sourceName === 'OperationMoneyDepo',
            $sourceName === 'OperationMoneyInOut' => 'MoneyInOut',
            str_starts_with($sourceName, 'OperationStock') => 'StockInOut',
            $sourceName === 'PortfolioMoney' => 'MoneyOnDate',
            $sourceName === 'PortfolioStockEX' => 'StockOnDate',
            $sourceName === 'PortfolioStockOTC',
            $sourceName === 'PortfolioStockBISLimitedStocks' => 'StockOnDate_NonExg',
            default => $recordType !== '' ? $recordType : $sourceName,
        };
    }
}
