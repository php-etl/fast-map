<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\MappingIteration;

use Kiboko\Component\Metadata\ClassTypeMetadata;
use Kiboko\Contract\Metadata\FieldMetadataInterface;
use Kiboko\Contract\Metadata\RelationMetadataInterface;

final readonly class ClassMappingIterator implements \RecursiveIterator
{
    private \Iterator $inner;

    public function __construct(private ClassTypeMetadata $metadata)
    {
        $this->inner = new \AppendIterator();
        $this->inner->append(new \ArrayIterator($this->metadata->getFields()));
        $this->inner->append(new \ArrayIterator($this->metadata->getRelations()));
    }

    public function current(): FieldMetadataInterface|RelationMetadataInterface
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
        /** @var FieldMetadataInterface|RelationMetadataInterface $current */
        $current = $this->inner->current();
        if (!$current instanceof RelationMetadataInterface) {
            throw new \RangeException('This item has no child.');
        }
        if ((is_countable($current->getType()) ? \count($current->getType()) : 0) > 1) {
            throw new \OutOfBoundsException('There is more than one type in the .');
        }

        return new self($current->getType());
    }
}
