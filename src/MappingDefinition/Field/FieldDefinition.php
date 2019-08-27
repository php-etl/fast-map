<?php

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Field;

use Kiboko\Component\ETL\Metadata\TypeMetadataInterface;

class FieldDefinition implements FieldDefinitionInterface
{
    /** @var string */
    public $name;
    /** @var TypeMetadataInterface[] */
    public $types;

    public function __construct(
        string $name,
        TypeMetadataInterface ...$types
    ) {
        $this->name = $name;
        $this->types = $types;
    }
}