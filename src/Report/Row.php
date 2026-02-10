<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Report;

use DateTimeImmutable;
use MasyaSmv\AtonStatementParser\Normalizers\DateNormalizer;
use MasyaSmv\AtonStatementParser\Normalizers\NumberNormalizer;

final class Row
{
    public function __construct(
        private string $section,
        /** @var array<string, string> */
        private array $attributes
    ) {
    }

    public function section(): string
    {
        return $this->section;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return $this->attributes;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    public function getString(string $key, ?string $default = null): ?string
    {
        return $this->attributes[$key] ?? $default;
    }

    public function getInt(string $key, ?int $default = null): ?int
    {
        $value = $this->getString($key);
        $int = NumberNormalizer::toInt($value);

        return $int ?? $default;
    }

    public function getFloat(string $key, ?float $default = null): ?float
    {
        $value = $this->getString($key);
        $float = NumberNormalizer::toFloat($value);

        return $float ?? $default;
    }

    public function getDecimalString(string $key, ?string $default = null): ?string
    {
        $value = $this->getString($key);
        $decimal = NumberNormalizer::toDecimalString($value);

        return $decimal ?? $default;
    }

    public function getBool(string $key, ?bool $default = null): ?bool
    {
        $value = $this->getString($key);
        $bool = NumberNormalizer::toBool($value);

        return $bool ?? $default;
    }

    public function getDate(string $key): ?DateTimeImmutable
    {
        $value = $this->getString($key);

        return DateNormalizer::toDate($value);
    }
}
