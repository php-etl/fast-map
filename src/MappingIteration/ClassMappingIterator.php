<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\MappingIteration;

use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use Kiboko\Component\ETL\Metadata\FieldMetadataInterface;
use Kiboko\Component\ETL\Metadata\RelationMetadataInterface;

final class ClassMappingIterator implements \RecursiveIterator
{
    /** @var ClassTypeMetadata  */
    private $metadata;
    /** @var \Iterator  */
    private $inner;

    public function __construct(ClassTypeMetadata $metadata)
    {
        $this->metadata = $metadata;
        $this->inner = new \AppendIterator();
        $this->inner->append(new \ArrayIterator($metadata->getFields()));
        $this->inner->append(new \ArrayIterator($metadata->getRelations()));
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
            throw new \RangeException('This item has no child.');
        }
        if (count($current->getTypes()) > 1) {
            throw new \OutOfBoundsException('There is more than one type in the .');
        }

        return new self($current->getTypes());
    }
}