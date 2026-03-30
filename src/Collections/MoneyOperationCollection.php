<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Collections;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use MasyaSmv\AtonStatementParser\Dto\MoneyOperation;
use OutOfBoundsException;
use Traversable;

/**
 * @implements IteratorAggregate<int, MoneyOperation>
 * @implements ArrayAccess<int, MoneyOperation>
 */
final class MoneyOperationCollection implements IteratorAggregate, Countable, ArrayAccess
{
    /** @var list<MoneyOperation> */
    private array $items;

    /** @param list<MoneyOperation> $items */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function first(): ?MoneyOperation
    {
        return $this->items[0] ?? null;
    }

    public function get(int $index): MoneyOperation
    {
        if (!array_key_exists($index, $this->items)) {
            throw new OutOfBoundsException('MoneyOperation index out of bounds: ' . $index);
        }

        return $this->items[$index];
    }

    /** @return list<MoneyOperation> */
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
    public function offsetGet(mixed $offset): MoneyOperation
    {
        if (!is_int($offset)) {
            throw new OutOfBoundsException('MoneyOperation index must be integer.');
        }

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('MoneyOperationCollection is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('MoneyOperationCollection is immutable.');
    }
}
