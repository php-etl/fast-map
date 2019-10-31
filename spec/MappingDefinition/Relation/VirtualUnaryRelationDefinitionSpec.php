<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\MappingDefinition\Relation;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Relation\VirtualUnaryRelationDefinition;
use Kiboko\Component\ETL\Metadata\CompositeTypeMetadataInterface;
use PhpSpec\ObjectBehavior;

final class VirtualUnaryRelationDefinitionSpec extends ObjectBehavior
{
    function it_is_initializable(CompositeTypeMetadataInterface $type)
    {
        $this->beConstructedWith(
            'foo',
            null,
            null,
            null,
            null,
            $type
        );
        $this->shouldHaveType(VirtualUnaryRelationDefinition::class);
    }
}
