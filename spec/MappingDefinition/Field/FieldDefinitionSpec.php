<?php

namespace spec\Kiboko\Component\ETL\FastMap\MappingDefinition\Field;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Field\FieldDefinition;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use Kiboko\Component\ETL\Metadata\CollectionTypeMetadata;
use Kiboko\Component\ETL\Metadata\ListTypeMetadata;
use Kiboko\Component\ETL\Metadata\ScalarTypeMetadata;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FieldDefinitionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('foo');
        $this->shouldHaveType(FieldDefinition::class);

        $this->name->shouldBeEqualTo('foo');
    }

    function it_is_accepting_one_scalar_type()
    {
        $this->beConstructedWith('foo', new ScalarTypeMetadata('string'));

        $this->types->shouldHaveCount(1);
    }

    function it_is_accepting_one_class_type()
    {
        $this->beConstructedWith('foo', new ClassTypeMetadata('stdClass'));

        $this->types->shouldHaveCount(1);
    }

    function it_is_accepting_one_collection_type()
    {
        $this->beConstructedWith(
            'foo',
            new CollectionTypeMetadata(
                new ClassTypeMetadata('stdClass'),
                new ScalarTypeMetadata('string')
            )
        );

        $this->types->shouldHaveCount(1);
    }

    function it_is_accepting_one_list_type()
    {
        $this->beConstructedWith(
            'foo',
            new ListTypeMetadata(
                new ScalarTypeMetadata('string')
            )
        );

        $this->types->shouldHaveCount(1);
    }

    function it_is_accepting_multiple_types()
    {
        $this->beConstructedWith(
            'foo',
            new ScalarTypeMetadata('string'),
            new ClassTypeMetadata('stdClass'),
            new CollectionTypeMetadata(
                new ClassTypeMetadata('stdClass'),
                new ScalarTypeMetadata('string')
            ),
            new ListTypeMetadata(
                new ScalarTypeMetadata('string')
            )
        );

        $this->types->shouldHaveCount(4);
        $this->types->shouldIterateLike(new \ArrayIterator([
            new ScalarTypeMetadata('string'),
            new ClassTypeMetadata('stdClass'),
            new CollectionTypeMetadata(
                new ClassTypeMetadata('stdClass'),
                new ScalarTypeMetadata('string')
            ),
            new ListTypeMetadata(
                new ScalarTypeMetadata('string')
            )
        ]));
    }
}
