<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Dto;

use DateTimeImmutable;

final class Trade
{
    public function __construct(
        private string $operId,
        private string $section,
        private string $sourceName,
        private ?bool $isComplete,
        private ?string $tradeType,
        private ?string $assetName,
        private ?string $quantity,
        private ?string $price,
        private ?string $priceCurrency,
        private ?string $payment,
        private ?string $paymentCurrency,
        private ?DateTimeImmutable $paymentDate,
        private ?DateTimeImmutable $settlementDate,
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

    public function isComplete(): ?bool
    {
        return $this->isComplete;
    }

    public function tradeType(): ?string
    {
        return $this->tradeType;
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

    public function priceCurrency(): ?string
    {
        return $this->priceCurrency;
    }

    public function payment(): ?string
    {
        return $this->payment;
    }

    public function paymentCurrency(): ?string
    {
        return $this->paymentCurrency;
    }

    public function paymentDate(): ?DateTimeImmutable
    {
        return $this->paymentDate;
    }

    public function settlementDate(): ?DateTimeImmutable
    {
        return $this->settlementDate;
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
