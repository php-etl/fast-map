<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\MappingIteration;

use Kiboko\Component\Metadata\ClassTypeMetadata;
use Kiboko\Contract\Metadata\CompositeTypeMetadataInterface;
use Kiboko\Contract\Metadata\FieldMetadataInterface;
use Kiboko\Contract\Metadata\RelationMetadataInterface;

final class MappingIterator implements \RecursiveIterator
{
    private \RecursiveIterator $inner;

    public function __construct(private readonly CompositeTypeMetadataInterface $metadata)
    {
        if ($this->metadata instanceof ClassTypeMetadata) {
            $this->inner = new ClassMappingIterator($this->metadata);
        } else {
            $this->inner = new \RecursiveArrayIterator([]);
        }
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

    public function hasChildren(): bool
    {
        /** @var FieldMetadataInterface|RelationMetadataInterface $current */
        $current = $this->inner->current();

        return $current instanceof RelationMetadataInterface;
    }

    public function getChildren(): self
    {
        return new self($this->inner->current());
    }
}
