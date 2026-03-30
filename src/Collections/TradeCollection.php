<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Collections;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use MasyaSmv\AtonStatementParser\Dto\Trade;
use OutOfBoundsException;
use Traversable;

/**
 * @implements IteratorAggregate<int, Trade>
 * @implements ArrayAccess<int, Trade>
 */
final class TradeCollection implements IteratorAggregate, Countable, ArrayAccess
{
    /** @var list<Trade> */
    private array $items;

    /** @param list<Trade> $items */
    public function __construct(array $items)
    {
        $this->items = array_values($items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function first(): ?Trade
    {
        return $this->items[0] ?? null;
    }

    public function get(int $index): Trade
    {
        if (!array_key_exists($index, $this->items)) {
            throw new OutOfBoundsException('Trade index out of bounds: ' . $index);
        }

        return $this->items[$index];
    }

    /** @return list<Trade> */
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

    public function offsetGet(mixed $offset): Trade
    {
        if (!is_int($offset)) {
            throw new OutOfBoundsException('Trade index must be integer.');
        }

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('TradeCollection is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('TradeCollection is immutable.');
    }
}
