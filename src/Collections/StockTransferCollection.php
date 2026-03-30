<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Collections;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use MasyaSmv\AtonStatementParser\Dto\StockTransfer;
use OutOfBoundsException;
use Traversable;

/**
 * @implements IteratorAggregate<int, StockTransfer>
 * @implements ArrayAccess<int, StockTransfer>
 */
final class StockTransferCollection implements IteratorAggregate, Countable, ArrayAccess
{
    /** @var list<StockTransfer> */
    private array $items;

    /** @param list<StockTransfer> $items */
    public function __construct(array $items)
    {
        $this->items = array_values($items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function first(): ?StockTransfer
    {
        return $this->items[0] ?? null;
    }

    public function get(int $index): StockTransfer
    {
        if (!array_key_exists($index, $this->items)) {
            throw new OutOfBoundsException('StockTransfer index out of bounds: ' . $index);
        }

        return $this->items[$index];
    }

    /** @return list<StockTransfer> */
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

    public function offsetGet(mixed $offset): StockTransfer
    {
        if (!is_int($offset)) {
            throw new OutOfBoundsException('StockTransfer index must be integer.');
        }

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('StockTransferCollection is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('StockTransferCollection is immutable.');
    }
}
