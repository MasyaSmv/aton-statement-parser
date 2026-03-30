<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Dto;

use DateTimeImmutable;

final class MoneyConvertOperation
{
    public function __construct(
        private string $operId,
        private string $section,
        private string $sourceName,
        private ?string $currencyFrom,
        private ?string $amountFrom,
        private ?string $amountFromRur,
        private ?DateTimeImmutable $dateFrom,
        private ?string $rate,
        private ?string $currencyTo,
        private ?string $amountTo,
        private ?string $amountToRur,
        private ?DateTimeImmutable $dateTo,
        private ?DateTimeImmutable $operDate
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

    public function currencyFrom(): ?string
    {
        return $this->currencyFrom;
    }

    public function amountFrom(): ?string
    {
        return $this->amountFrom;
    }

    public function amountFromRur(): ?string
    {
        return $this->amountFromRur;
    }

    public function dateFrom(): ?DateTimeImmutable
    {
        return $this->dateFrom;
    }

    public function rate(): ?string
    {
        return $this->rate;
    }

    public function currencyTo(): ?string
    {
        return $this->currencyTo;
    }

    public function amountTo(): ?string
    {
        return $this->amountTo;
    }

    public function amountToRur(): ?string
    {
        return $this->amountToRur;
    }

    public function dateTo(): ?DateTimeImmutable
    {
        return $this->dateTo;
    }

    public function operDate(): ?DateTimeImmutable
    {
        return $this->operDate;
    }
}
