<?php

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Field\FieldDefinitionInterface;
use Kiboko\Component\ETL\Metadata\ClassMetadata;

interface FieldDefinitionGuesserInterface
{
    /**
     * @param ClassMetadata $class
     *
     * @return FieldDefinitionInterface[]
     */
    public function __invoke(ClassMetadata $class): \Generator;
}