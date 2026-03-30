<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Dto;

use DateTimeImmutable;

final class CommonData
{
    public function __construct(
        private string $cpId,
        private ?DateTimeImmutable $begDate,
        private ?DateTimeImmutable $endDate,
        private ?DateTimeImmutable $makeDate,
        private ?string $contractNum,
        private ?DateTimeImmutable $contractDate,
        private ?bool $withSubAccounts,
        private ?bool $isFilial,
        private ?string $companyName
    ) {
    }

    public function cpId(): string
    {
        return $this->cpId;
    }

    public function begDate(): ?DateTimeImmutable
    {
        return $this->begDate;
    }

    public function endDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function makeDate(): ?DateTimeImmutable
    {
        return $this->makeDate;
    }

    public function contractNum(): ?string
    {
        return $this->contractNum;
    }

    public function contractDate(): ?DateTimeImmutable
    {
        return $this->contractDate;
    }

    public function withSubAccounts(): ?bool
    {
        return $this->withSubAccounts;
    }

    public function isFilial(): ?bool
    {
        return $this->isFilial;
    }

    public function companyName(): ?string
    {
        return $this->companyName;
    }
}
