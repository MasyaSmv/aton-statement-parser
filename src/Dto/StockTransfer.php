<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Dto;

use DateTimeImmutable;

final class StockTransfer
{
    public function __construct(
        private string $operId,
        private string $section,
        private string $sourceName,
        private ?string $assetName,
        private ?string $quantity,
        private ?string $price,
        private ?string $portfolio,
        private ?string $comment,
        private ?string $intOperNum,
        private ?DateTimeImmutable $operDate,
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

    public function price(): ?string
    {
        return $this->price;
    }

    public function portfolio(): ?string
    {
        return $this->portfolio;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function intOperNum(): ?string
    {
        return $this->intOperNum;
    }

    public function operDate(): ?DateTimeImmutable
    {
        return $this->operDate;
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
