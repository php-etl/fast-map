<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\PropertyAccess;

use Symfony\Component\PropertyAccess\Exception\OutOfBoundsException;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class EmptyPropertyPath implements \IteratorAggregate, PropertyPathInterface, \Stringable
{
    public function __toString(): string
    {
        return '';
    }

    public function getLength(): int
    {
        return 0;
    }

    public function getParent(): ?PropertyPathInterface
    {
        return null;
    }

    public function getIterator(): \Traversable
    {
        return new \EmptyIterator();
    }

    public function getElements(): array
    {
        return [];
    }

    public function getElement($index): string
    {
        throw new OutOfBoundsException(sprintf('The index %s is not within the property path', $index));
    }

    public function isProperty($index): bool
    {
        throw new OutOfBoundsException(sprintf('The index %s is not within the property path', $index));
    }

    public function isIndex($index): bool
    {
        throw new OutOfBoundsException(sprintf('The index %s is not within the property path', $index));
    }
}
