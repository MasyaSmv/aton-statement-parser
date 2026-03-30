<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Report;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use OutOfBoundsException;
use Traversable;

/**
 * @implements IteratorAggregate<int, ParseDiagnostic>
 * @implements ArrayAccess<int, ParseDiagnostic>
 */
final class DiagnosticCollection implements IteratorAggregate, Countable, ArrayAccess
{
    /** @var list<ParseDiagnostic> */
    private array $items;

    /** @param list<ParseDiagnostic> $items */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function first(): ?ParseDiagnostic
    {
        return $this->items[0] ?? null;
    }

    public function get(int $index): ?ParseDiagnostic
    {
        return $this->items[$index] ?? null;
    }

    /** @return list<ParseDiagnostic> */
    public function toArray(): array
    {
        return $this->items;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /** @psalm-suppress RedundantConditionGivenDocblockType */
    public function offsetExists(mixed $offset): bool
    {
        return is_int($offset) && isset($this->items[$offset]);
    }

    /** @psalm-suppress DocblockTypeContradiction */
    public function offsetGet(mixed $offset): ?ParseDiagnostic
    {
        if (!is_int($offset)) {
            throw new OutOfBoundsException('Diagnostic index must be an integer.');
        }

        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('DiagnosticCollection is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('DiagnosticCollection is immutable.');
    }
}
