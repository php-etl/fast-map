<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\MappingIteration;

use Kiboko\Contract\Metadata\TypeMetadataInterface;
use Symfony\Component\PropertyAccess\PropertyPathIteratorInterface;

final readonly class PathMappingIterator implements \Iterator
{
    public function __construct(private PropertyPathIteratorInterface $inner, private TypeMetadataInterface $metadata)
    {
    }

    public function current(): mixed
    {
        return $this->inner->current();
    }

    public function next(): void
    {
        $this->inner->next();
    }

    public function key(): mixed
    {
        return $this->inner->key();
    }

    public function valid(): bool
    {
        return $this->inner->valid();
    }

    public function rewind(): void
    {
        $this->inner->rewind();
    }
}
