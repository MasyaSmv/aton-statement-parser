<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Collections;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use OutOfBoundsException;
use Traversable;

/**
 * @implements IteratorAggregate<int, string>
 * @implements ArrayAccess<mixed, string>
 */
final class OperIdCollection implements IteratorAggregate, Countable, ArrayAccess
{
    /** @var list<string> */
    private array $items;

    /** @param list<string> $items */
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

    public function get(int $index): string
    {
        if (!array_key_exists($index, $this->items)) {
            throw new OutOfBoundsException('OperID index out of bounds: ' . $index);
        }

        return $this->items[$index];
    }

    public function first(): ?string
    {
        return $this->items[0] ?? null;
    }

    /** @return list<string> */
    public function toArray(): array
    {
        return $this->items;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return is_int($offset) && array_key_exists($offset, $this->items);
    }

    public function offsetGet(mixed $offset): string
    {
        if (!is_int($offset)) {
            throw new OutOfBoundsException('OperID index must be integer.');
        }

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('OperIdCollection is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('OperIdCollection is immutable.');
    }
}
