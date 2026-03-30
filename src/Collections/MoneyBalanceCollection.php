<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Collections;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use MasyaSmv\AtonStatementParser\Dto\MoneyBalance;
use OutOfBoundsException;
use Traversable;

/**
 * @implements IteratorAggregate<int, MoneyBalance>
 * @implements ArrayAccess<mixed, MoneyBalance>
 */
final class MoneyBalanceCollection implements IteratorAggregate, Countable, ArrayAccess
{
    /** @var list<MoneyBalance> */
    private array $items;

    /** @param list<MoneyBalance> $items */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function first(): ?MoneyBalance
    {
        return $this->items[0] ?? null;
    }

    public function get(int $index): MoneyBalance
    {
        if (!array_key_exists($index, $this->items)) {
            throw new OutOfBoundsException('MoneyBalance index out of bounds: ' . $index);
        }

        return $this->items[$index];
    }

    /** @return list<MoneyBalance> */
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

    public function offsetGet(mixed $offset): MoneyBalance
    {
        if (!is_int($offset)) {
            throw new OutOfBoundsException('MoneyBalance index must be integer.');
        }

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('MoneyBalanceCollection is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('MoneyBalanceCollection is immutable.');
    }
}
