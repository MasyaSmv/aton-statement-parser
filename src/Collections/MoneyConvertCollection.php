<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Collections;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use MasyaSmv\AtonStatementParser\Dto\MoneyConvertOperation;
use OutOfBoundsException;
use Traversable;

/**
 * @implements IteratorAggregate<int, MoneyConvertOperation>
 * @implements ArrayAccess<int, MoneyConvertOperation>
 */
final class MoneyConvertCollection implements IteratorAggregate, Countable, ArrayAccess
{
    /** @var list<MoneyConvertOperation> */
    private array $items;

    /** @param list<MoneyConvertOperation> $items */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function first(): ?MoneyConvertOperation
    {
        return $this->items[0] ?? null;
    }

    public function get(int $index): MoneyConvertOperation
    {
        if (!array_key_exists($index, $this->items)) {
            throw new OutOfBoundsException('MoneyConvertOperation index out of bounds: ' . $index);
        }

        return $this->items[$index];
    }

    /** @return list<MoneyConvertOperation> */
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
    public function offsetGet(mixed $offset): MoneyConvertOperation
    {
        if (!is_int($offset)) {
            throw new OutOfBoundsException('MoneyConvertOperation index must be integer.');
        }

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('MoneyConvertCollection is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('MoneyConvertCollection is immutable.');
    }
}
