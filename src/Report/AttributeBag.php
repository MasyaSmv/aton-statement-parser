<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Report;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use Traversable;

/**
 * @implements IteratorAggregate<string, string>
 * @implements ArrayAccess<string, string>
 */
final class AttributeBag implements IteratorAggregate, Countable, ArrayAccess
{
    /** @var array<string, string> */
    private array $values;

    /** @param array<string, string> $values */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->values[$key] ?? $default;
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return $this->values;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->values);
    }

    /** @psalm-suppress RedundantConditionGivenDocblockType */
    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) && array_key_exists($offset, $this->values);
    }

    /** @psalm-suppress DocblockTypeContradiction */
    public function offsetGet(mixed $offset): ?string
    {
        if (!is_string($offset)) {
            return null;
        }

        return $this->values[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('AttributeBag is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('AttributeBag is immutable.');
    }
}
