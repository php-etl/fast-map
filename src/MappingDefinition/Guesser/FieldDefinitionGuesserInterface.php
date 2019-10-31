<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Field\FieldDefinitionInterface;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;

interface FieldDefinitionGuesserInterface
{
    /**
     * @param ClassTypeMetadata $class
     *
     * @return FieldDefinitionInterface[]|\Generator
     */
    public function __invoke(ClassTypeMetadata $class): \Iterator;
}