<?php

namespace spec\Kiboko\Component\ETL\FastMap\MappingDefinition\Field;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Field\VirtualFieldDefinition;
use Kiboko\Component\ETL\Metadata\ArgumentMetadata;
use Kiboko\Component\ETL\Metadata\ArgumentMetadataList;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use Kiboko\Component\ETL\Metadata\CollectionTypeMetadata;
use Kiboko\Component\ETL\Metadata\ListTypeMetadata;
use Kiboko\Component\ETL\Metadata\MethodMetadata;
use Kiboko\Component\ETL\Metadata\ScalarTypeMetadata;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class VirtualFieldDefinitionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('foo');
        $this->shouldHaveType(VirtualFieldDefinition::class);

        $this->name->shouldBeEqualTo('foo');
        $this->accessor->shouldBeNull();
        $this->mutator->shouldBeNull();
        $this->checker->shouldBeNull();
        $this->remover->shouldBeNull();
    }

    function it_is_using_accessor()
    {
        $this->beConstructedWith(
            'foo',
            new MethodMetadata(
                'getFoo',
                new ArgumentMetadataList(),
                new ScalarTypeMetadata('string')
            )
        );

        $this->accessor->shouldBeLike(
            new MethodMetadata(
                'getFoo',
                new ArgumentMetadataList(),
                new ScalarTypeMetadata('string')
            )
        );
        $this->mutator->shouldBeNull();
        $this->checker->shouldBeNull();
        $this->remover->shouldBeNull();
    }

    function it_is_using_mutator()
    {
        $this->beConstructedWith(
            'foo',
            null,
            new MethodMetadata(
                'setFoo',
                new ArgumentMetadataList(new ArgumentMetadata(new ScalarTypeMetadata('string')))
            )
        );

        $this->accessor->shouldBeNull();
        $this->mutator->shouldBeLike(
            new MethodMetadata(
                'setFoo',
                new ArgumentMetadataList(new ArgumentMetadata(new ScalarTypeMetadata('string')))
            )
        );
        $this->checker->shouldBeNull();
        $this->remover->shouldBeNull();
    }

    function it_is_using_checker()
    {
        $this->beConstructedWith(
            'foo',
            null,
            null,
            new MethodMetadata(
                'hasFoo',
                new ArgumentMetadataList(),
                new ScalarTypeMetadata('bool')
            )
        );

        $this->accessor->shouldBeNull();
        $this->mutator->shouldBeNull();
        $this->checker->shouldBeLike(
            new MethodMetadata(
                'hasFoo',
                new ArgumentMetadataList(),
                new ScalarTypeMetadata('bool')
            )
        );
        $this->remover->shouldBeNull();
    }

    function it_is_using_remover()
    {
        $this->beConstructedWith(
            'foo',
            null,
            null,
            null,
            new MethodMetadata(
                'unsetFoo',
                new ArgumentMetadataList()
            )
        );

        $this->accessor->shouldBeNull();
        $this->mutator->shouldBeNull();
        $this->checker->shouldBeNull();
        $this->remover->shouldBeLike(
            new MethodMetadata(
                'unsetFoo',
                new ArgumentMetadataList()
            )
        );
    }
}
