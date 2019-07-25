<?php

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Field\FieldDefinition;
use Kiboko\Component\ETL\Metadata\ClassMetadata;

class PublicPropertyFieldGuesser implements FieldDefinitionGuesserInterface
{
    public function __invoke(ClassMetadata $class): \Generator
    {
        foreach ($class->properties as $property) {
            yield new FieldDefinition(
                $property->name,
                $property->types
            );
        }
    }
}