<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Dto;

final class StockBalance
{
    public function __construct(
        private string $section,
        private string $sourceName,
        private ?string $assetCode,
        private ?string $quantityIn,
        private ?string $quantityInAvailable,
        private ?string $quantityPlan,
        private ?string $quantityOut,
        private ?string $quantityOutAvailable,
        private ?string $priceIn,
        private ?string $priceOut,
        private ?string $currencyIn,
        private ?string $currencyOut,
        private ?string $valueIn,
        private ?string $valueOut,
        private ?string $nkdIn,
        private ?string $nkdOut
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

    public function quantityIn(): ?string
    {
        return $this->quantityIn;
    }

    public function quantityInAvailable(): ?string
    {
        return $this->quantityInAvailable;
    }

    public function quantityPlan(): ?string
    {
        return $this->quantityPlan;
    }

    public function quantityOut(): ?string
    {
        return $this->quantityOut;
    }

    public function quantityOutAvailable(): ?string
    {
        return $this->quantityOutAvailable;
    }

    public function priceIn(): ?string
    {
        return $this->priceIn;
    }

    public function priceOut(): ?string
    {
        return $this->priceOut;
    }

    public function currencyIn(): ?string
    {
        return $this->currencyIn;
    }

    public function currencyOut(): ?string
    {
        return $this->currencyOut;
    }

    public function valueIn(): ?string
    {
        return $this->valueIn;
    }

    public function valueOut(): ?string
    {
        return $this->valueOut;
    }

    public function nkdIn(): ?string
    {
        return $this->nkdIn;
    }

    public function nkdOut(): ?string
    {
        return $this->nkdOut;
    }
}
