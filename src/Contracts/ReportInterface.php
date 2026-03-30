<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Contracts;

use MasyaSmv\AtonStatementParser\Collections\CorporateActionCollection;
use MasyaSmv\AtonStatementParser\Collections\MoneyBalanceCollection;
use MasyaSmv\AtonStatementParser\Collections\MoneyConvertCollection;
use MasyaSmv\AtonStatementParser\Collections\MoneyOperationCollection;
use MasyaSmv\AtonStatementParser\Collections\OperIdCollection;
use MasyaSmv\AtonStatementParser\Collections\StockBalanceCollection;
use MasyaSmv\AtonStatementParser\Collections\StockPayingOffCollection;
use MasyaSmv\AtonStatementParser\Collections\StockTransferCollection;
use MasyaSmv\AtonStatementParser\Collections\TradeCollection;
use MasyaSmv\AtonStatementParser\Dto\CommonData;
use MasyaSmv\AtonStatementParser\Report\DiagnosticCollection;
use MasyaSmv\AtonStatementParser\Report\Row;
use MasyaSmv\AtonStatementParser\Report\Section;

interface ReportInterface
{
    public function hasSection(string $name): bool;

    public function section(string $name): Section;

    public function operIds(): OperIdCollection;

    public function findOperId(string $operId): ?Row;

    public function commonData(): ?CommonData;

    public function trades(): TradeCollection;

    public function moneyInOut(): MoneyOperationCollection;

    public function moneyOnDate(): MoneyBalanceCollection;

    public function stockOnDate(): StockBalanceCollection;

    public function moneyConvert(): MoneyConvertCollection;

    public function stockInOut(): StockTransferCollection;

    public function stockPayingOff(): StockPayingOffCollection;

    public function corporateActionsIn(): CorporateActionCollection;

    public function corporateActionsOut(): CorporateActionCollection;

    public function diagnostics(): DiagnosticCollection;

    public function hasDiagnostics(): bool;
}
