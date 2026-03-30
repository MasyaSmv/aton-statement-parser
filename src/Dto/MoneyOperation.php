<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Dto;

use DateTimeImmutable;

final class MoneyOperation
{
    public function __construct(
        private string $operId,
        private string $section,
        private string $sourceName,
        private ?string $operType,
        private ?string $quantity,
        private ?string $quantityRur,
        private ?string $currency,
        private ?string $comment,
        private ?DateTimeImmutable $operDate,
        private ?DateTimeImmutable $paymentDate,
        private ?DateTimeImmutable $operDateSort
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

    public function operType(): ?string
    {
        return $this->operType;
    }

    public function quantity(): ?string
    {
        return $this->quantity;
    }

    public function quantityRur(): ?string
    {
        return $this->quantityRur;
    }

    public function currency(): ?string
    {
        return $this->currency;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function operDate(): ?DateTimeImmutable
    {
        return $this->operDate;
    }

    public function paymentDate(): ?DateTimeImmutable
    {
        return $this->paymentDate;
    }

    public function operDateSort(): ?DateTimeImmutable
    {
        return $this->operDateSort;
    }
}
