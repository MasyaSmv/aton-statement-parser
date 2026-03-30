<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Parsing;

final class KnownModernSchema
{
    /** @var array<string, list<string>> */
    private const FIELDS_BY_SOURCE = [
        'Header' => ['ChiefOfficeShortName', 'CompanyName', 'Contract', 'ContractDate', 'ContractNum', 'CpID', 'DateBegin', 'DateEnd', 'DirectorShortName', 'IsFilial', 'IsOneDayReport', 'IsSignatureVisible', 'LogoImageBase64', 'ReportDate', 'SignatureImageBase64', 'Title', 'URLToReport', 'WithSubaccount'],
        'OperationMoneyBrok' => ['Comment', 'Currency', 'OperDate', 'OperID', 'PaymentDate', 'Portfolio', 'Quantity', 'QuantityRUR'],
        'OperationMoneyDepo' => ['Comment', 'Currency', 'OperDate', 'OperID', 'PaymentDate', 'Portfolio', 'Quantity', 'QuantityRUR'],
        'OperationMoneyInOut' => ['Comment', 'Currency', 'OperDate', 'OperID', 'PaymentDate', 'Portfolio', 'Quantity', 'QuantityRUR'],
        'OperationStockCorpActionIn' => ['AssetName', 'ExSettlementDate', 'IntOperNum', 'Nominal', 'NominalCurr', 'OperDate', 'OperID', 'PaymentDate', 'Portfolio', 'Quantity', 'SettlementDate'],
        'OperationStockCorpActionOut' => ['AssetName', 'ExSettlementDate', 'IntOperNum', 'Nominal', 'NominalCurr', 'OperDate', 'OperID', 'PaymentDate', 'Portfolio', 'Quantity', 'SettlementDate'],
        'OperationStockInOut' => ['AssetName', 'Comment', 'ExSettlementDate', 'IntOperNum', 'OperDate', 'OperID', 'Portfolio', 'Price', 'Quantity', 'SettlementDate'],
        'OperationStockPayOff' => ['AssetName', 'ExSettlementDate', 'IntOperNum', 'Nominal', 'NominalCurr', 'OperDate', 'OperID', 'PayingSum', 'PayingSumRUR', 'PaymentCurr', 'PaymentDate', 'Portfolio', 'Quantity', 'SettlementDate'],
        'PortfolioMoney' => ['AssetCode', 'Part', 'PartName', 'PartOrder', 'QuantityBegin', 'QuantityBeginRUR', 'QuantityEnd', 'QuantityEndRUR', 'RecordOrder', 'Section', 'SectionName'],
        'PortfolioStockBISLimitedStocks' => ['AmountBegin', 'AmountEnd', 'AssetCode', 'NKDBegin', 'NKDEnd', 'PriceBegin', 'PriceCurrBegin', 'PriceCurrEnd', 'PriceEnd', 'QuantityAvailableBegin', 'QuantityAvailableEnd', 'QuantityBegin', 'QuantityEnd', 'QuantitytWillBe'],
        'PortfolioStockEX' => ['AmountBegin', 'AmountEnd', 'AssetCode', 'NKDBegin', 'NKDEnd', 'OperPlace', 'PriceBegin', 'PriceCurrBegin', 'PriceCurrEnd', 'PriceEnd', 'QuantityAvailableBegin', 'QuantityAvailableEnd', 'QuantityBegin', 'QuantityEnd', 'QuantitytWillBe'],
        'PortfolioStockMetal' => ['AmountBegin', 'AmountEnd', 'AssetCode', 'NKDBegin', 'NKDEnd', 'OperPlace', 'PriceBegin', 'PriceCurrEnd', 'PriceEnd', 'QuantityAvailableBegin', 'QuantityAvailableEnd', 'QuantityBegin', 'QuantityEnd', 'QuantitytWillBe'],
        'PortfolioStockOTC' => ['AmountBegin', 'AmountEnd', 'AssetCode', 'NKDBegin', 'NKDEnd', 'PriceBegin', 'PriceCurrBegin', 'PriceCurrEnd', 'PriceEnd', 'QuantityAvailableBegin', 'QuantityAvailableEnd', 'QuantityBegin', 'QuantityEnd', 'QuantitytWillBe'],
        'TradeCommonNotSettled' => ['AssetName', 'ComDlr', 'ComDlrCurrency', 'ComExg', 'ComTS', 'ExSettlementDate', 'IntOperNum', 'MarginOrder', 'NKD', 'NKD_RUR', 'OperID', 'OperOrder', 'OperPlace', 'OrderTerm', 'Payment', 'PaymentCurr', 'PaymentDate', 'Payment_RUR', 'Portfolio', 'Price', 'PriceCurr', 'Quantity', 'SettlementDate', 'TradeState', 'TradeStateName', 'TradeType'],
        'TradeCommonSettled' => ['AssetName', 'ComDlr', 'ComDlrCurrency', 'ComExg', 'ComTS', 'ExSettlementDate', 'IntOperNum', 'MarginOrder', 'NKD', 'NKD_RUR', 'OperID', 'OperOrder', 'OperPlace', 'OrderTerm', 'Payment', 'PaymentCurr', 'PaymentDate', 'Payment_RUR', 'Portfolio', 'Price', 'PriceCurr', 'Quantity', 'SettlementDate', 'TradeState', 'TradeStateName', 'TradeType'],
        'TradeFXClient' => ['ComSum', 'Curr1', 'Curr2', 'Date1', 'Date2', 'IntOperNum', 'OperID', 'Portfolio', 'Rate', 'Sum1', 'Sum1RUR', 'Sum2', 'Sum2RUR'],
        'TradeFXNonClient' => ['ComSum', 'Curr1', 'Curr2', 'Date1', 'Date2', 'IntOperNum', 'OperID', 'Portfolio', 'Rate', 'Sum1', 'Sum1RUR', 'Sum2', 'Sum2RUR'],
        'TradeRepoNotSettled' => ['AssetName', 'ComDlr', 'ComExg', 'ComTS', 'IntOperNum', 'Leg', 'NKD', 'NKD_RUR', 'OperID', 'OperPlace', 'OrderTerm', 'Payment', 'PaymentCurr', 'PaymentDate', 'Payment_RUR', 'Portfolio', 'Price', 'PriceCurr', 'Quantity', 'RepoMarginCall', 'RepoRate', 'SettlementDate', 'TradeState', 'TradeStateName', 'TradeType'],
        'TradeRepoSettled' => ['AssetName', 'ComDlr', 'ComExg', 'ComTS', 'ExSettlementDate', 'IntOperNum', 'Leg', 'NKD', 'NKD_RUR', 'OperID', 'OperPlace', 'OrderTerm', 'Payment', 'PaymentCurr', 'PaymentDate', 'Payment_RUR', 'Portfolio', 'Price', 'PriceCurr', 'Quantity', 'RepoMarginCall', 'RepoRate', 'SettlementDate', 'TradeState', 'TradeStateName', 'TradeType'],
    ];

    public static function isKnownSource(string $sourceName): bool
    {
        return isset(self::FIELDS_BY_SOURCE[$sourceName]);
    }

    /** @return list<string>|null */
    public static function allowedFieldsForSource(string $sourceName): ?array
    {
        return self::FIELDS_BY_SOURCE[$sourceName] ?? null;
    }
}
