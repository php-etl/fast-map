<?php

namespace spec\Kiboko\Component\ETL\FastMap\MappingDefinition\Relation;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Relation\VirtualMultipleRelationDefinition;
use Kiboko\Component\ETL\Metadata\IterableTypeMetadataInterface;
use Kiboko\Component\ETL\Metadata\MethodMetadata;
use PhpSpec\ObjectBehavior;

class VirtualMultipleRelationDefinitionSpec extends ObjectBehavior
{
    function it_is_initializable(IterableTypeMetadataInterface $type)
    {
        $this->beConstructedWith(
            'foo',
            null,
            null,
            null,
            null,
            null,
            null,
            $type
        );
        $this->shouldHaveType(VirtualMultipleRelationDefinition::class);
    }
}
