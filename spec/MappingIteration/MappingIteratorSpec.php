<?php declare(strict_types=1);

namespace spec\Kiboko\Component\ETL\FastMap\MappingIteration;

use Kiboko\Component\ETL\FastMap\MappingIteration\MappingIterator;
use Kiboko\Component\ETL\Metadata\CompositeTypeMetadataInterface;
use PhpSpec\ObjectBehavior;

final class MappingIteratorSpec extends ObjectBehavior
{
    function it_is_initializable(CompositeTypeMetadataInterface $metadata)
    {
        $this->beConstructedWith($metadata);
        $this->shouldHaveType(MappingIterator::class);
    }

    function it_is_iterable(
        CompositeTypeMetadataInterface $metadata
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

        $this->beConstructedWith($metadata);
        $this->shouldIterateAs(new \ArrayIterator($objects));
    }
}
