<?php

namespace Kiboko\Component\ETL\FastMap\MappingDefinition;

use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use Kiboko\Component\ETL\Metadata\CompositeTypeMetadataInterface;

class MappedClassType implements CompositeTypeMetadataInterface
{
    /** @var ClassTypeMetadata|null */
    public $metadata;
    /** @var Field\FieldDefinition[] */
    public $fields;
    /** @var Relation\RelationDefinitionInterface[] */
    public $relations;

    public function __construct(?ClassTypeMetadata $metadata)
    {
        $this->metadata = $metadata;
        $this->fields = [];
        $this->relations = [];
    }

    public function fields(Field\FieldDefinitionInterface ...$fields): self
    {
        foreach ($fields as $field) {
            $this->fields[$field->name] = $field;
        }

        return $this;
    }

    public function relations(Relation\RelationDefinitionInterface ...$relations): self
    {
        foreach ($relations as $relation) {
            $this->relations[$relation->name] = $relation;
        }

        return $this;
    }

    public function __toString()
    {
        return (string) $this->metadata;
    }
}