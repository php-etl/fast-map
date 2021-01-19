<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\MappingIteration;

use Kiboko\Component\Metadata\ClassTypeMetadata;
use Kiboko\Component\Metadata\CompositeTypeMetadataInterface;
use Kiboko\Component\Metadata\FieldMetadataInterface;
use Kiboko\Component\Metadata\RelationMetadataInterface;

final class MappingIterator implements \RecursiveIterator
{
    /** @var CompositeTypeMetadataInterface  */
    private $metadata;
    /** @var \RecursiveIterator  */
    private $inner;

    public function __construct(CompositeTypeMetadataInterface $metadata)
    {
        $this->metadata = $metadata;

        if ($metadata instanceof ClassTypeMetadata) {
            $this->inner = new ClassMappingIterator($metadata);
        } else {
            $this->inner = new \RecursiveArrayIterator([]);
        }
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
        return new self($this->inner->current());
    }
}
