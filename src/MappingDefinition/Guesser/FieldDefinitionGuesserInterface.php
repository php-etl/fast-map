<?php

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Field\FieldDefinitionInterface;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;

interface FieldDefinitionGuesserInterface
{
    /**
     * @param ClassTypeMetadata $class
     *
     * @return FieldDefinitionInterface[]
     */
    public function __invoke(ClassTypeMetadata $class): \Generator;
}