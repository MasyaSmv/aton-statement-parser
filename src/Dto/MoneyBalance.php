<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Dto;

final class MoneyBalance
{
    public function __construct(
        private string $section,
        private string $sourceName,
        private ?string $assetCode,
        private ?string $name,
        private ?string $part,
        private ?string $partName,
        private ?string $quantityBegin,
        private ?string $quantityEnd,
        private ?string $quantityBeginRur,
        private ?string $quantityEndRur
    ) {
    }

    public function section(): string
    {
        return $this->section;
    }

    public function sourceName(): string
    {
        return $this->sourceName;
    }

    public function assetCode(): ?string
    {
        return $this->assetCode;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function part(): ?string
    {
        return $this->part;
    }

    public function partName(): ?string
    {
        return $this->partName;
    }

    public function quantityBegin(): ?string
    {
        return $this->quantityBegin;
    }

    public function quantityEnd(): ?string
    {
        return $this->quantityEnd;
    }

    public function quantityBeginRur(): ?string
    {
        return $this->quantityBeginRur;
    }

    public function quantityEndRur(): ?string
    {
        return $this->quantityEndRur;
    }
}
