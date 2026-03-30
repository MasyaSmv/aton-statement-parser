<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Report;

final class ParseDiagnostic
{
    public function __construct(
        private string $code,
        private string $message,
        private string $format,
        private ?string $structure = null,
        private ?string $key = null
    ) {
    }

    public function code(): string
    {
        return $this->code;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function format(): string
    {
        return $this->format;
    }

    public function structure(): ?string
    {
        return $this->structure;
    }

    public function key(): ?string
    {
        return $this->key;
    }
}
