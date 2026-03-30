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
            $sourceName === 'PortfolioMoney' => 'MoneyOnDate',
            $sourceName === 'PortfolioStockEX' => 'StockOnDate_Exg',
            $sourceName === 'PortfolioStockOTC' => 'StockOnDate',
            $sourceName === 'PortfolioStockBISLimitedStocks' => 'StockOnDate_NonExg',
            $sourceName === 'PortfolioStockMetal' => 'StockOnDate_MTL',
            default => $recordType !== '' ? $recordType : $sourceName,
        };
    }
}
