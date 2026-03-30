<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Report;

use MasyaSmv\AtonStatementParser\Collections\CorporateActionCollection;
use MasyaSmv\AtonStatementParser\Collections\MoneyBalanceCollection;
use MasyaSmv\AtonStatementParser\Collections\MoneyConvertCollection;
use MasyaSmv\AtonStatementParser\Collections\MoneyOperationCollection;
use MasyaSmv\AtonStatementParser\Collections\OperIdCollection;
use MasyaSmv\AtonStatementParser\Collections\RowCollection;
use MasyaSmv\AtonStatementParser\Collections\StockBalanceCollection;
use MasyaSmv\AtonStatementParser\Collections\StockPayingOffCollection;
use MasyaSmv\AtonStatementParser\Collections\StockTransferCollection;
use MasyaSmv\AtonStatementParser\Collections\TradeCollection;
use MasyaSmv\AtonStatementParser\Contracts\Mappers\CommonDataMapperInterface;
use MasyaSmv\AtonStatementParser\Contracts\Mappers\CorporateActionMapperInterface;
use MasyaSmv\AtonStatementParser\Contracts\Mappers\MoneyBalanceMapperInterface;
use MasyaSmv\AtonStatementParser\Contracts\Mappers\MoneyConvertMapperInterface;
use MasyaSmv\AtonStatementParser\Contracts\Mappers\MoneyOperationMapperInterface;
use MasyaSmv\AtonStatementParser\Contracts\Mappers\StockBalanceMapperInterface;
use MasyaSmv\AtonStatementParser\Contracts\Mappers\StockPayingOffMapperInterface;
use MasyaSmv\AtonStatementParser\Contracts\Mappers\StockTransferMapperInterface;
use MasyaSmv\AtonStatementParser\Contracts\Mappers\TradeMapperInterface;
use MasyaSmv\AtonStatementParser\Contracts\ReportInterface;
use MasyaSmv\AtonStatementParser\Dto\CommonData;
use MasyaSmv\AtonStatementParser\Exceptions\ParseException;
use MasyaSmv\AtonStatementParser\Mappers\CommonDataMapper;
use MasyaSmv\AtonStatementParser\Mappers\CorporateActionMapper;
use MasyaSmv\AtonStatementParser\Mappers\MoneyBalanceMapper;
use MasyaSmv\AtonStatementParser\Mappers\MoneyConvertMapper;
use MasyaSmv\AtonStatementParser\Mappers\MoneyOperationMapper;
use MasyaSmv\AtonStatementParser\Mappers\StockBalanceMapper;
use MasyaSmv\AtonStatementParser\Mappers\StockPayingOffMapper;
use MasyaSmv\AtonStatementParser\Mappers\StockTransferMapper;
use MasyaSmv\AtonStatementParser\Mappers\TradeMapper;

final class Report implements ReportInterface
{
    /** @var array<string, Section> */
    private array $sections = [];

    private CommonDataMapperInterface $commonDataMapper;

    private TradeMapperInterface $tradeMapper;

    private MoneyOperationMapperInterface $moneyOperationMapper;

    private MoneyBalanceMapperInterface $moneyBalanceMapper;

    private MoneyConvertMapperInterface $moneyConvertMapper;

    private StockBalanceMapperInterface $stockBalanceMapper;

    private StockTransferMapperInterface $stockTransferMapper;

    private StockPayingOffMapperInterface $stockPayingOffMapper;

    private CorporateActionMapperInterface $corporateActionMapper;

    private function __construct(
        ?CommonDataMapperInterface $commonDataMapper = null,
        ?TradeMapperInterface $tradeMapper = null,
        ?MoneyOperationMapperInterface $moneyOperationMapper = null,
        ?MoneyBalanceMapperInterface $moneyBalanceMapper = null,
        ?MoneyConvertMapperInterface $moneyConvertMapper = null,
        ?StockBalanceMapperInterface $stockBalanceMapper = null,
        ?StockTransferMapperInterface $stockTransferMapper = null,
        ?StockPayingOffMapperInterface $stockPayingOffMapper = null,
        ?CorporateActionMapperInterface $corporateActionMapper = null
    ) {
        $this->commonDataMapper = $commonDataMapper ?? new CommonDataMapper();
        $this->tradeMapper = $tradeMapper ?? new TradeMapper();
        $this->moneyOperationMapper = $moneyOperationMapper ?? new MoneyOperationMapper();
        $this->moneyBalanceMapper = $moneyBalanceMapper ?? new MoneyBalanceMapper();
        $this->moneyConvertMapper = $moneyConvertMapper ?? new MoneyConvertMapper();
        $this->stockBalanceMapper = $stockBalanceMapper ?? new StockBalanceMapper();
        $this->stockTransferMapper = $stockTransferMapper ?? new StockTransferMapper();
        $this->stockPayingOffMapper = $stockPayingOffMapper ?? new StockPayingOffMapper();
        $this->corporateActionMapper = $corporateActionMapper ?? new CorporateActionMapper();
    }

    /**
     * @param array<string, list<Row>> $rowsBySection
     */
    public static function fromRowsBySection(array $rowsBySection): self
    {
        $self = new self();

        foreach ($rowsBySection as $sectionName => $rows) {
            if ($rows === []) {
                continue;
            }

            $self->sections[$sectionName] = new Section($sectionName, new RowCollection($rows));
        }

        return $self;
    }

    public function hasSection(string $name): bool
    {
        return isset($this->sections[$name]);
    }

    public function section(string $name): Section
    {
        if (!isset($this->sections[$name])) {
            throw new ParseException('Section not found: ' . $name);
        }

        return $this->sections[$name];
    }

    public function operIds(): OperIdCollection
    {
        $ids = [];

        foreach ($this->sections as $section) {
            foreach ($section->rows() as $row) {
                $id = $row->getString('OperID');

                if ($id !== null && $id !== '') {
                    $ids[] = $id;
                }
            }
        }

        return new OperIdCollection(array_values(array_unique($ids)));
    }

    public function findOperId(string $operId): ?Row
    {
        foreach ($this->sections as $section) {
            foreach ($section->rows() as $row) {
                if ($row->getString('OperID') === $operId) {
                    return $row;
                }
            }
        }

        return null;
    }

    public function commonData(): ?CommonData
    {
        if (!$this->hasSection('CommonData')) {
            return null;
        }

        $row = $this->section('CommonData')->rows()->first();

        return $row instanceof Row ? $this->commonDataMapper->map($row) : null;
    }

    public function trades(): TradeCollection
    {
        $rows = [];

        foreach (['Trades', 'TradesRegRepo', 'TradesNonRegRepo'] as $sectionName) {
            if (!$this->hasSection($sectionName)) {
                continue;
            }

            foreach ($this->section($sectionName)->rows() as $row) {
                $rows[] = $this->tradeMapper->map($row);
            }
        }

        return new TradeCollection($rows);
    }

    public function moneyInOut(): MoneyOperationCollection
    {
        $rows = [];

        foreach (['MoneyInOut', 'MoneyInOut_io'] as $sectionName) {
            if (!$this->hasSection($sectionName)) {
                continue;
            }

            foreach ($this->section($sectionName)->rows() as $row) {
                $rows[] = $this->moneyOperationMapper->map($row);
            }
        }

        return new MoneyOperationCollection($rows);
    }

    public function moneyOnDate(): MoneyBalanceCollection
    {
        $rows = [];

        if ($this->hasSection('MoneyOnDate')) {
            foreach ($this->section('MoneyOnDate')->rows() as $row) {
                $rows[] = $this->moneyBalanceMapper->map($row);
            }
        }

        return new MoneyBalanceCollection($rows);
    }

    public function stockOnDate(): StockBalanceCollection
    {
        $rows = [];

        foreach (['StockOnDate', 'StockOnDate_Exg', 'StockOnDate_NonExg', 'StockOnDate_MTL'] as $sectionName) {
            if (!$this->hasSection($sectionName)) {
                continue;
            }

            foreach ($this->section($sectionName)->rows() as $row) {
                $rows[] = $this->stockBalanceMapper->map($row);
            }
        }

        return new StockBalanceCollection($rows);
    }

    public function moneyConvert(): MoneyConvertCollection
    {
        $rows = [];

        foreach (['ClientMoneyConvert', 'MoneyConvert'] as $sectionName) {
            if (!$this->hasSection($sectionName)) {
                continue;
            }

            foreach ($this->section($sectionName)->rows() as $row) {
                $rows[] = $this->moneyConvertMapper->map($row);
            }
        }

        return new MoneyConvertCollection($rows);
    }

    public function stockInOut(): StockTransferCollection
    {
        $rows = [];

        if ($this->hasSection('StockInOut')) {
            foreach ($this->section('StockInOut')->rows() as $row) {
                $rows[] = $this->stockTransferMapper->map($row);
            }
        }

        return new StockTransferCollection($rows);
    }

    public function stockPayingOff(): StockPayingOffCollection
    {
        $rows = [];

        if ($this->hasSection('StockPayingOff')) {
            foreach ($this->section('StockPayingOff')->rows() as $row) {
                $rows[] = $this->stockPayingOffMapper->map($row);
            }
        }

        return new StockPayingOffCollection($rows);
    }

    public function corporateActionsIn(): CorporateActionCollection
    {
        return $this->mapCorporateActionsSection('CorpActionIn');
    }

    public function corporateActionsOut(): CorporateActionCollection
    {
        return $this->mapCorporateActionsSection('CorpActionOut');
    }

    private function mapCorporateActionsSection(string $sectionName): CorporateActionCollection
    {
        $rows = [];

        if ($this->hasSection($sectionName)) {
            foreach ($this->section($sectionName)->rows() as $row) {
                $rows[] = $this->corporateActionMapper->map($row);
            }
        }

        return new CorporateActionCollection($rows);
    }
}
