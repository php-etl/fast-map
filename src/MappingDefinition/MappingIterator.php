<?php

namespace Kiboko\Component\ETL\FastMap\MappingDefinition;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Field\FieldDefinitionInterface;
use Kiboko\Component\ETL\FastMap\MappingDefinition\Relation\RelationDefinitionInterface;
use Kiboko\Component\ETL\Metadata\CompositeTypeMetadataInterface;
use Kiboko\Component\ETL\Metadata\Type;
use Kiboko\Component\ETL\Metadata\TypeMetadataInterface;

class MappingIterator implements \RecursiveIterator
{
    /** @var CompositeTypeMetadataInterface */
    private $metadata;
    /** @var \RecursiveIterator */
    private $inner;
    /** @var MappingIterationFactoryInterface[] */
    private $builders;

    public function __construct(TypeMetadataInterface $metadata, MappingIterationFactoryInterface ...$builders)
    {
        $this->metadata = $metadata;
        $this->builders = $builders;

        $this->inner = new \EmptyIterator();
        foreach ($builders as $builder) {
            if ($builder->matches($metadata)) {
                $this->inner = $builder->walk($metadata);
                break;
            }
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
        /** @var FieldDefinitionInterface|RelationDefinitionInterface $current */
        $current = $this->inner->current();
        return $current instanceof RelationDefinitionInterface;
    }

    public function getChildren()
    {
        return new self(
            $this->current(),
            ...$this->builders
        );
    }
}