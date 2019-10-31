<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Relation\UnaryRelationDefinition;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use Kiboko\Component\ETL\Metadata\CompositeTypeMetadataInterface;
use Kiboko\Component\ETL\Metadata\TypeMetadataInterface;

class PublicPropertyUnaryRelationGuesser implements RelationDefinitionGuesserInterface
{
    public function __invoke(ClassTypeMetadata $class): \Generator
    {
        foreach ($class->properties as $property) {
            $types = iterator_to_array($this->filterTypes(...$property->types));
            if (count($types) <= 0) {
                continue;
            }

            yield new UnaryRelationDefinition(
                $property->name,
                ...$types
            );
        }
    }

    private function filterTypes(TypeMetadataInterface ...$types): \Generator
    {
        foreach ($types as $type) {
            if (!$type instanceof CompositeTypeMetadataInterface) {
                continue;
            }

            yield $type;
        }
    }
}