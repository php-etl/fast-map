<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\MappingDefinition\Field;

use Kiboko\Component\ETL\FastMap\MappingDefinition\Field\VirtualFieldDefinition;
use Kiboko\Component\ETL\Metadata\ArgumentMetadata;
use Kiboko\Component\ETL\Metadata\ArgumentMetadataList;
use Kiboko\Component\ETL\Metadata\MethodMetadata;
use Kiboko\Component\ETL\Metadata\ScalarTypeMetadata;
use PhpSpec\ObjectBehavior;

final class VirtualFieldDefinitionSpec extends ObjectBehavior
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

        $this->accessor->shouldBeAnInstanceOf(MethodMetadata::class);
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
                new ArgumentMetadataList(new ArgumentMetadata('foo', new ScalarTypeMetadata('string')))
            )
        );

        $this->accessor->shouldBeNull();
        $this->mutator->shouldBeAnInstanceOf(MethodMetadata::class);
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
        $this->checker->shouldBeAnInstanceOf(MethodMetadata::class);
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
        $this->remover->shouldBeAnInstanceOf(MethodMetadata::class);
    }
}
