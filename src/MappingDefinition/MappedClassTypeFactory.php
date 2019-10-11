<?php

namespace Kiboko\Component\ETL\FastMap\MappingDefinition;

use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;

class MappedClassTypeFactory
{
    /** @var Guesser\FieldDefinitionGuesserInterface */
    private $fieldGuesser;
    /** @var Guesser\RelationDefinitionGuesserInterface */
    private $relationGuesser;

    public function __construct(
        Guesser\FieldDefinitionGuesserInterface $fieldGuesser,
        Guesser\RelationDefinitionGuesserInterface $relationGuesser
    ) {
        $this->fieldGuesser = $fieldGuesser;
        $this->relationGuesser = $relationGuesser;
    }

    public function __invoke(ClassTypeMetadata $class): MappedClassType
    {
        return (new MappedClassType($class))
            ->fields(...($this->fieldGuesser)($class))
            ->relations(...($this->relationGuesser)($class));
    }
}