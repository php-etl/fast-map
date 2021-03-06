<?php declare(strict_types=1);

namespace Kiboko\Component\FastMap\MappingIteration;

use Kiboko\Component\Metadata\ClassTypeMetadata;
use Kiboko\Contract\Metadata\CompositeTypeMetadataInterface;
use Kiboko\Contract\Metadata\FieldMetadataInterface;
use Kiboko\Contract\Metadata\RelationMetadataInterface;

final class MappingIterator implements \RecursiveIterator
{
    private \RecursiveIterator $inner;

    public function __construct(private CompositeTypeMetadataInterface $metadata)
    {
        if ($this->metadata instanceof ClassTypeMetadata) {
            $this->inner = new ClassMappingIterator($this->metadata);
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

    public function valid(): bool
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

    public function getChildren(): MappingIterator
    {
        return new self($this->inner->current());
    }
}
