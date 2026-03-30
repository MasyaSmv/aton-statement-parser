<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Collections;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use MasyaSmv\AtonStatementParser\Report\Row;
use OutOfBoundsException;
use Traversable;

/**
 * @implements IteratorAggregate<int, Row>
 * @implements ArrayAccess<int, Row>
 */
final class RowCollection implements IteratorAggregate, Countable, ArrayAccess
{
    /** @var list<Row> */
    private array $items;

    /** @param list<Row> $items */
    public function __construct(array $items)
    {
        $this->items = array_values($items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function get(int $index): Row
    {
        if (!array_key_exists($index, $this->items)) {
            throw new OutOfBoundsException('Row index out of bounds: ' . $index);
        }

        return $this->items[$index];
    }

    public function first(): ?Row
    {
        return $this->items[0] ?? null;
    }

    /** @return list<Row> */
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

    public function offsetGet(mixed $offset): Row
    {
        if (!is_int($offset)) {
            throw new OutOfBoundsException('Row index must be integer.');
        }

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('RowCollection is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('RowCollection is immutable.');
    }
}
