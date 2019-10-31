<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\MappingDefinition;

use Kiboko\Component\ETL\Metadata\TypeMetadataInterface;

class ClassMappingIterationFactory implements MappingIterationFactoryInterface
{
    public function matches(TypeMetadataInterface $subject): bool
    {
        return $subject instanceof MappedClassType;
    }

    public function walk(TypeMetadataInterface $subject): \Iterator
    {
        /** @var MappedClassType $subject */
        yield from $subject->fields;
        yield from $subject->relations;
    }

}