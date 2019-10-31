<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\MappingDefinition\Relation;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Relation\MultipleRelationDefinition;
use Kiboko\Component\ETL\Metadata\IterableTypeMetadataInterface;
use PhpSpec\ObjectBehavior;

final class MultipleRelationDefinitionSpec extends ObjectBehavior
{
    function it_is_initializable(IterableTypeMetadataInterface $type)
    {
        $this->beConstructedWith('foo', $type);
        $this->shouldHaveType(MultipleRelationDefinition::class);
    }
}
