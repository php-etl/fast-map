<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\MappingIteration;

use Kiboko\Contract\Metadata\TypeMetadataInterface;
use Symfony\Component\PropertyAccess\PropertyPathIteratorInterface;

final class PathMappingIterator implements \Iterator
{
    public function __construct(private PropertyPathIteratorInterface $inner, private TypeMetadataInterface $metadata)
    {
    }

    public function current()
    {
        return $this->inner->current();
    }

    public function next()
    {
        $this->inner->next();
    }

    public function key()
    {
        return $this->inner->key();
    }

    public function valid(): bool
    {
        return $this->inner->valid();
    }

    public function rewind()
    {
        $this->inner->rewind();
    }
}
