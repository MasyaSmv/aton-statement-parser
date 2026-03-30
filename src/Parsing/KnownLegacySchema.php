<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Parsing;

final class KnownLegacySchema
{
    /** @var array<string, list<string>> */
    private const FIELDS_BY_SECTION = [
        'ClientMoneyConvert' => ['ComSum', 'Curr1', 'Curr2', 'Date1', 'Date2', 'IntOperNum', 'OperDate', 'OperDateSort', 'OperID', 'OperTime', 'Portfolio', 'Rate', 'Section', 'Sum1', 'Sum1_RUR', 'Sum2', 'Sum2_RUR'],
        'CommonData' => ['BegDate', 'CPID', 'ChiefOfficeShortName', 'CompanyName', 'ContractDate', 'ContractNum', 'DirectorShortName', 'EndDate', 'IsFilial', 'LogoImageBase64', 'LogoURL', 'MakeDate', 'SBSType', 'URLToReport', 'WithSubAccounts'],
        'CorpActionIn' => ['AssetName', 'GroupID', 'IntOperNum', 'Nominal', 'NominalCurr', 'OperDate', 'OperDateSort', 'OperID', 'OperTimeSort', 'PayingSum', 'PayingSum_RUR', 'PaymentDate', 'Portfolio', 'Quantity', 'SettlementDate'],
        'CorpActionOut' => ['AssetName', 'GroupID', 'IntOperNum', 'Nominal', 'NominalCurr', 'OperDate', 'OperDateSort', 'OperID', 'OperTimeSort', 'PayingSum', 'PayingSum_RUR', 'PaymentDate', 'Portfolio', 'Quantity', 'SettlementDate'],
        'MoneyConvert' => ['ComSum', 'Curr1', 'Curr2', 'Date1', 'Date2', 'IntOperNum', 'OperDate', 'OperDateSort', 'OperID', 'Portfolio', 'Rate', 'Section', 'Sum1', 'Sum1_RUR', 'Sum2', 'Sum2_RUR'],
        'MoneyInOut' => ['Comment', 'Currency', 'IsIBStock', 'OperDate', 'OperDateSort', 'OperID', 'OperTimeSort', 'OperType', 'OperTypeSort', 'PaymentDate', 'Portfolio', 'Quantity', 'SettlementDate'],
        'MoneyInOut_io' => ['Comment', 'Currency', 'IntOperType', 'OperDate', 'OperDateSort', 'OperID', 'OperTimeSort', 'OperType', 'OperTypeSort', 'PaymentDate', 'Portfolio', 'Quantity', 'Quantity_RUR'],
        'MoneyOnDate' => ['AssetCode', 'Name', 'QtyBeg', 'QtyBeg_rub', 'QtyEnd', 'QtyEnd_rub', 'n1', 'n2'],
        'MoneyOnDate_MarketPrc' => ['AssetCode', 'Name', 'QtyBeg', 'QtyBeg_rub', 'QtyEnd', 'QtyEnd_rub', 'n1', 'n2'],
        'MoneyOnDate_single' => ['ChangeDebtsMZ', 'MicexRenamePAODate', 'MoneyFSIn', 'MoneyFSOut', 'MoneyGKOIn', 'MoneyGKOOut', 'MoneyGTSGPIn', 'MoneyGTSGPOut'],
        'StockInOut' => ['AssetName', 'Comment', 'ExSettlementDate', 'IntOperNum', 'OperDate', 'OperDateSort', 'OperID', 'OperTimeSort', 'Portfolio', 'Price', 'Quantity', 'Section', 'SettlementDate'],
        'StockOnDate' => ['AssetCode', 'CurrencyIn', 'CurrencyOut', 'NKDIn', 'NKDOut', 'OperPlace', 'PriceIn', 'PriceOut', 'QttyIn', 'QttyInAft', 'QttyOut', 'QttyOutAft', 'QttyPlan', 'ValueIn', 'ValueOut'],
        'StockOnDate_Exg' => ['AssetCode', 'CurrencyIn', 'CurrencyOut', 'IsOTC', 'NKDIn', 'NKDOut', 'OperPlace', 'PriceIn', 'PriceOut', 'QttyIn', 'QttyInAft', 'QttyOut', 'QttyOutAft', 'QttyPlan', 'ValueIn', 'ValueOut'],
        'StockOnDate_Exg_Sum' => ['NKDIn', 'NKDOut', 'ValueIn', 'ValueOut'],
        'StockOnDate_MTL' => ['AssetCode', 'OperPlace', 'PriceOut', 'QttyIn', 'QttyOut', 'QttyPlan', 'ValueIn', 'ValueOut'],
        'StockOnDate_NonExg' => ['AssetCode', 'CurrencyIn', 'CurrencyOut', 'NKDIn', 'NKDOut', 'OperPlace', 'PriceIn', 'PriceOut', 'QttyIn', 'QttyInAft', 'QttyOut', 'QttyOutAft', 'QttyPlan', 'ValueIn', 'ValueOut'],
        'StockPayingOff' => ['AssetName', 'GroupID', 'IntOperNum', 'IntOperType', 'Nominal', 'NominalCurr', 'OperDate', 'OperDateSort', 'OperID', 'OperTimeSort', 'PayingSum', 'PayingSum_RUR', 'PaymentCurr', 'PaymentDate', 'Portfolio', 'Quantity', 'SettlementDate'],
        'Trades' => ['AssetName', 'ComDlr', 'ComDlrCurrency', 'ComExg', 'ComTS', 'IntOperNum', 'IsIB', 'IsOperWithoutPrice', 'IsPif', 'NKD', 'NKD_RUR', 'OperDateSort', 'OperID', 'OperPlace', 'OperTimeSort', 'OrderTerm', 'Payment', 'PaymentCurr', 'PaymentDate', 'Payment_RUR', 'Portfolio', 'Price', 'PriceCurr', 'Quantity', 'SettlementDate', 'TradeType', 'isComplete', 'isRPS'],
        'TradesNonRegRepo' => ['AssetName', 'ComDlr', 'ComExg', 'ComTS', 'ExSettlementDate', 'IntOperNum', 'LinkOperID', 'NKD', 'NKD_RUR', 'OperDateSort', 'OperID', 'OperPlace', 'OperTimeSort', 'OrderTerm', 'Payment', 'PaymentCurr', 'PaymentDate', 'Payment_RUR', 'Portfolio', 'Price', 'PriceCurr', 'Quantity', 'RepoDiscount', 'RepoMarginCall', 'RepoPeriod', 'RepoRate', 'SettlementDate', 'SortRepo', 'TradeType', 'isComplete', 'isRPS'],
        'TradesRegRepo' => ['AssetName', 'ComDlr', 'ComExg', 'ComTS', 'ExSettlementDate', 'IntOperNum', 'LinkOperID', 'NKD', 'NKD_RUR', 'OperDateSort', 'OperID', 'OperPlace', 'OperTimeSort', 'OrderTerm', 'Payment', 'PaymentCurr', 'PaymentDate', 'Payment_RUR', 'Portfolio', 'Price', 'PriceCurr', 'Quantity', 'RepoDiscount', 'RepoPeriod', 'RepoRate', 'SettlementDate', 'SortRepo', 'TradeType', 'isComplete', 'isRPS'],
    ];

    public static function isKnownSection(string $sectionName): bool
    {
        return isset(self::FIELDS_BY_SECTION[$sectionName]);
    }

    /** @return list<string>|null */
    public static function allowedFieldsForSection(string $sectionName): ?array
    {
        return self::FIELDS_BY_SECTION[$sectionName] ?? null;
    }
}
