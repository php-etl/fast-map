<?php

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Field\FieldDefinition;
use Kiboko\Component\ETL\Metadata\ArrayTypeMetadata;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use Kiboko\Component\ETL\Metadata\ScalarTypeMetadata;
use Kiboko\Component\ETL\Metadata\TypeMetadata;

class PublicPropertyFieldGuesser implements FieldDefinitionGuesserInterface
{
    public function __invoke(ClassTypeMetadata $class): \Generator
    {
        foreach ($class->properties as $property) {
            $types = iterator_to_array($this->filterTypes(...$property->types));
            if (count($types) <= 0) {
                continue;
            }

            yield new FieldDefinition(
                $property->name,
                ...array_values($property->types)
            );
        }
    }

    private function filterTypes(TypeMetadata ...$types): \Generator
    {
        foreach ($types as $type) {
            if (!$type instanceof ScalarTypeMetadata &&
                !$type instanceof ArrayTypeMetadata
            ) {
                continue;
            }

            yield $type;
        }
    }
}