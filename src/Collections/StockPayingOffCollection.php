<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Collections;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use MasyaSmv\AtonStatementParser\Dto\StockPayingOff;
use OutOfBoundsException;
use Traversable;

/**
 * @implements IteratorAggregate<int, StockPayingOff>
 * @implements ArrayAccess<int, StockPayingOff>
 */
final class StockPayingOffCollection implements IteratorAggregate, Countable, ArrayAccess
{
    /** @var list<StockPayingOff> */
    private array $items;

    /** @param list<StockPayingOff> $items */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function first(): ?StockPayingOff
    {
        return $this->items[0] ?? null;
    }

    public function get(int $index): StockPayingOff
    {
        if (!array_key_exists($index, $this->items)) {
            throw new OutOfBoundsException('StockPayingOff index out of bounds: ' . $index);
        }

        return $this->items[$index];
    }

    /** @return list<StockPayingOff> */
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
        return is_int($offset) && array_key_exists($offset, $this->items);
    }

    /** @psalm-suppress DocblockTypeContradiction */
    public function offsetGet(mixed $offset): StockPayingOff
    {
        if (!is_int($offset)) {
            throw new OutOfBoundsException('StockPayingOff index must be integer.');
        }

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('StockPayingOffCollection is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('StockPayingOffCollection is immutable.');
    }
}
