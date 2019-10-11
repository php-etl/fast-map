<?php

namespace spec\Kiboko\Component\ETL\FastMap\MappingDefinition\Relation;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Relation\UnaryRelationDefinition;
use Kiboko\Component\ETL\Metadata\CompositeTypeMetadataInterface;
use PhpSpec\ObjectBehavior;

class UnaryRelationDefinitionSpec extends ObjectBehavior
{
    function it_is_initializable(CompositeTypeMetadataInterface $type)
    {
        $this->beConstructedWith('foo', $type);
        $this->shouldHaveType(UnaryRelationDefinition::class);
    }

    function it_is_named(CompositeTypeMetadataInterface $type)
    {
        $this->beConstructedWith('foo', $type);
        $this->name->shouldBeEqualTo('foo');
    }
}
