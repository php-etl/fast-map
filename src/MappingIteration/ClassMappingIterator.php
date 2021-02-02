<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\MappingIteration;

use Kiboko\Component\Metadata\ClassTypeMetadata;
use Kiboko\Contract\Metadata\FieldMetadataInterface;
use Kiboko\Contract\Metadata\RelationMetadataInterface;

final class ClassMappingIterator implements \RecursiveIterator
{
    private \Iterator $inner;

    public function __construct(private ClassTypeMetadata $metadata)
    {
        $this->inner = new \AppendIterator();
        $this->inner->append(new \ArrayIterator($this->metadata->getFields()));
        $this->inner->append(new \ArrayIterator($this->metadata->getRelations()));
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

    public function hasChildren(): bool
    {
        /** @var FieldMetadataInterface|RelationMetadataInterface $current */
        $current = $this->inner->current();
        return $current instanceof RelationMetadataInterface;
    }

    public function getChildren(): ClassMappingIterator
    {
        /** @var FieldMetadataInterface|RelationMetadataInterface $current */
        $current = $this->inner->current();
        if (!$current instanceof RelationMetadataInterface) {
            throw new \RangeException('This item has no child.');
        }
        if (count($current->getTypes()) > 1) {
            throw new \OutOfBoundsException('There is more than one type in the .');
        }

        return new self($current->getTypes());
    }
}
