<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser;

use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;

class RelationDefinitionGuesserChain implements RelationDefinitionGuesserInterface
{
    /** @var RelationDefinitionGuesserInterface[] */
    private $inner;

    public function __construct(RelationDefinitionGuesserInterface ...$inner)
    {
        $this->inner = $inner;
    }

    public function __invoke(ClassTypeMetadata $class): \Generator
    {
        foreach ($this->inner as $guesser) {
            yield from $guesser($class);
        }
    }
}