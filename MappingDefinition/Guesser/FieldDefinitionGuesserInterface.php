<?php

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser;

use Kiboko\Component\ETL\Metadata\ClassMetadata;

interface FieldDefinitionGuesserInterface
{
    public function __invoke(ClassMetadata $class): \Generator;
}