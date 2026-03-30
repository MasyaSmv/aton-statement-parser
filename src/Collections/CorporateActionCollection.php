<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Collections;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use MasyaSmv\AtonStatementParser\Dto\CorporateAction;
use OutOfBoundsException;
use Traversable;

/**
 * @implements IteratorAggregate<int, CorporateAction>
 * @implements ArrayAccess<int, CorporateAction>
 */
final class CorporateActionCollection implements IteratorAggregate, Countable, ArrayAccess
{
    /** @var list<CorporateAction> */
    private array $items;

    /** @param list<CorporateAction> $items */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function first(): ?CorporateAction
    {
        return $this->items[0] ?? null;
    }

    public function get(int $index): CorporateAction
    {
        if (!array_key_exists($index, $this->items)) {
            throw new OutOfBoundsException('CorporateAction index out of bounds: ' . $index);
        }

        return $this->items[$index];
    }

    /** @return list<CorporateAction> */
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
    public function offsetGet(mixed $offset): CorporateAction
    {
        if (!is_int($offset)) {
            throw new OutOfBoundsException('CorporateAction index must be integer.');
        }

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('CorporateActionCollection is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('CorporateActionCollection is immutable.');
    }
}
