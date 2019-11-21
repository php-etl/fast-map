<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\MappingIteration;

use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use Kiboko\Component\ETL\Metadata\FieldMetadataInterface;
use Kiboko\Component\ETL\Metadata\RelationMetadataInterface;

final class ClassMappingIterator implements \RecursiveIterator
{
    private ClassTypeMetadata $metadata;
    private \Iterator $inner;

    public function __construct(ClassTypeMetadata $metadata)
    {
        $this->metadata = $metadata;
        $this->inner = new \AppendIterator();
        $this->inner->append(new \ArrayIterator($metadata->fields));
        $this->inner->append(new \ArrayIterator($metadata->relations));
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

    public function valid()
    {
        return $this->inner->valid();
    }

    public function rewind()
    {
        $this->inner->rewind();
    }

    public function hasChildren()
    {
        /** @var FieldMetadataInterface|RelationMetadataInterface $current */
        $current = $this->inner->current();
        return $current instanceof RelationMetadataInterface;
    }

    public function getChildren()
    {
        /** @var FieldMetadataInterface|RelationMetadataInterface $current */
        $current = $this->inner->current();
        if (!$current instanceof RelationMetadataInterface) {
            throw new \RangeException('This item has no child');
        }

        return new self($current->);
    }
}