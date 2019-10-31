<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\MappingDefinition;

use Kiboko\Component\ETL\FastMap\MappingDefinition\MappingIterationFactoryInterface;
use Kiboko\Component\ETL\FastMap\MappingDefinition\MappingIterator;
use Kiboko\Component\ETL\Metadata\TypeMetadataInterface;
use PhpSpec\ObjectBehavior;

final class MappingIteratorSpec extends ObjectBehavior
{
    function it_is_initializable(TypeMetadataInterface $metadata, MappingIterationFactoryInterface $builder)
    {
        $builder->matches($metadata)->willReturn(true);
        $builder->walk($metadata)->willReturn(new \EmptyIterator());

        $this->beConstructedWith($metadata, $builder);
        $this->shouldHaveType(MappingIterator::class);
    }

    function it_is_iterable(
        TypeMetadataInterface $metadata,
        MappingIterationFactoryInterface $builder
    ) {
        $objects = [];

        $objects[] = new class {
            public $lorem = 'ipsum';
        };
        $objects[] = new class {
            public $dolor = 'sit';
        };
        $objects[] = new class {
            public $amet = 'consecutir';
        };
        $objects[] = new class {
            public $sid = 'nolem';
        };

        $builder->matches($metadata)->willReturn(true);
        $builder->walk($metadata)->willReturn(new \ArrayIterator($objects));

        $this->beConstructedWith($metadata, $builder);
        $this->shouldIterateAs(new \ArrayIterator($objects));
    }
}
