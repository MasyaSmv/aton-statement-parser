<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Dto;

use DateTimeImmutable;

final class CorporateAction
{
    public function __construct(
        private string $operId,
        private string $section,
        private string $sourceName,
        private ?string $assetName,
        private ?string $quantity,
        private ?string $nominal,
        private ?string $nominalCurrency,
        private ?string $payingSum,
        private ?string $payingSumRur,
        private ?string $groupId,
        private ?string $portfolio,
        private ?string $intOperNum,
        private ?DateTimeImmutable $operDate,
        private ?DateTimeImmutable $paymentDate,
        private ?DateTimeImmutable $settlementDate,
        private ?DateTimeImmutable $exSettlementDate,
        private ?DateTimeImmutable $operDateSort,
        private ?DateTimeImmutable $operTimeSort
    ) {
    }

    public function operId(): string
    {
        return $this->operId;
    }

    public function section(): string
    {
        return $this->section;
    }

    public function sourceName(): string
    {
        return $this->sourceName;
    }

    public function assetName(): ?string
    {
        return $this->assetName;
    }

    public function quantity(): ?string
    {
        return $this->quantity;
    }

    public function nominal(): ?string
    {
        return $this->nominal;
    }

    public function nominalCurrency(): ?string
    {
        return $this->nominalCurrency;
    }

    public function payingSum(): ?string
    {
        return $this->payingSum;
    }

    public function payingSumRur(): ?string
    {
        return $this->payingSumRur;
    }

    public function groupId(): ?string
    {
        return $this->groupId;
    }

    public function portfolio(): ?string
    {
        return $this->portfolio;
    }

    public function intOperNum(): ?string
    {
        return $this->intOperNum;
    }

    public function operDate(): ?DateTimeImmutable
    {
        return $this->operDate;
    }

    public function paymentDate(): ?DateTimeImmutable
    {
        return $this->paymentDate;
    }

    public function settlementDate(): ?DateTimeImmutable
    {
        return $this->settlementDate;
    }

    public function exSettlementDate(): ?DateTimeImmutable
    {
        return $this->exSettlementDate;
    }

    public function operDateSort(): ?DateTimeImmutable
    {
        return $this->operDateSort;
    }

    public function operTimeSort(): ?DateTimeImmutable
    {
        return $this->operTimeSort;
    }
}
