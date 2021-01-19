<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\PropertyAccess;

use Symfony\Component\PropertyAccess\Exception\OutOfBoundsException;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class EmptyPropertyPath implements \IteratorAggregate, PropertyPathInterface
{
    public function __toString()
    {
        return '';
    }

    public function getLength()
    {
        return 0;
    }

    public function getParent()
    {
        return null;
    }

    public function getIterator()
    {
        return new \EmptyIterator();
    }

    public function getElements()
    {
        return [];
    }

    public function getElement($index)
    {
        throw new OutOfBoundsException(sprintf('The index %s is not within the property path', $index));
    }

    public function isProperty($index)
    {
        throw new OutOfBoundsException(sprintf('The index %s is not within the property path', $index));
    }

    public function isIndex($index)
    {
        throw new OutOfBoundsException(sprintf('The index %s is not within the property path', $index));
    }
}
