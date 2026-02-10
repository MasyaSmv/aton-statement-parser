<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Report;

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
}
