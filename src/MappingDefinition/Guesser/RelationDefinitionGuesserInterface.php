<?php

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Relation\RelationDefinitionInterface;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;

interface RelationDefinitionGuesserInterface
{
    /**
     * @param ClassTypeMetadata $class
     *
     * @return RelationDefinitionInterface[]
     */
    public function __invoke(ClassTypeMetadata $class): \Generator;
}