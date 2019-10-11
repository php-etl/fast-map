<?php

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Relation;

use Kiboko\Component\ETL\Metadata\IterableTypeMetadataInterface;
use Kiboko\Component\ETL\Metadata\TypeMetadataInterface;

class MultipleRelationDefinition implements RelationDefinitionInterface
{
    /** @var string */
    public $name;
    /** @var TypeMetadataInterface[] */
    public $types;

    public function __construct(
        string $name,
        IterableTypeMetadataInterface ...$types
    ) {
        $this->name = $name;
        $this->types = $types;
    }
}