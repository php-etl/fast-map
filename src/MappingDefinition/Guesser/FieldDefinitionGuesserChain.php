<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser;

use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;

class FieldDefinitionGuesserChain implements FieldDefinitionGuesserInterface
{
    /** @var FieldDefinitionGuesserInterface[] */
    private $inner;

    public function __construct(FieldDefinitionGuesserInterface ...$inner)
    {
        $this->inner = $inner;
    }

    public function __invoke(ClassTypeMetadata $class): \Iterator
    {
        foreach ($this->inner as $guesser) {
            yield from $guesser($class);
        }
    }
}