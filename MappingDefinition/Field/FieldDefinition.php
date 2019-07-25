<?php

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Field;

use Kiboko\Component\ETL\Metadata\TypeMetadata;

class FieldDefinition implements FieldDefinitionInterface
{
    /** @var string */
    public $name;
    /** @var TypeMetadata[] */
    public $types;

    public function __construct(
        string $name,
        TypeMetadata ...$types
    ) {
        $this->types = $types;
        $this->name = $name;
    }
}